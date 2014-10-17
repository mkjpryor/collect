<?hh // strict

namespace Mkjp\Collect;

use Mkjp\Option\Option;


/**
* Trait implementing the vector interface using lazy, immutable implementations
* 
* Requires using classes to implement getIterator only, although other methods
* can be overridden for efficiency purposes
*/
trait VectorTrait<Tv> {
    require implements CollectVector<Tv>;
    
    use IterableTrait<Tv>;
    
    
    public function append(Tv $item): CollectVector<Tv> {
        return new Stream(function() use($item) {
            foreach( $this as $e ) yield $e;
            yield $item;
        });
    }
    
    public function at(int $index): Option<Tv> {
        if( $index < 0 ) return Option::none();
        
        foreach( $this as $e ) {
            if( $index === 0 ) return Option::just($e);
            $index--;
        }
        return Option::none();
    }
    
    public function filterWithIndex((function(Tv, int): bool) $p): CollectVector<Tv> {
        return new Stream(function() use($p) {
            $i = 0;
            foreach( $this as $e ) {
                if( $p($e, $i) ) yield $e;
                $i++;
            }
        });
    }
    
    public function indexOf(Tv $item, int $from = 0, int $count = -1): int {
        return $this->indexWhere($x ==> $x === $item, $from, $count);
    }
    
    public function indexWhere((function(Tv): bool) $p, int $from = 0, int $count = -1): int {
        $i = -1;
        foreach( $this as $e ) {
            $i++;
            if( $i < $from ) continue;
            if( $count >= 0 && $i >= $from + $count ) break;
            if( $p($e) ) return $i;
        }
        return -1;
    }
    
    public function lastIndexOf(Tv $item, int $from = 0, int $count = -1): int {
        return $this->lastIndexWhere($x ==> $x === $item, $from, $count);
    }

    public function lastIndexWhere((function(Tv): bool) $p, int $from = 0, int $count = -1): int {
        $i = $result = -1;
        foreach( $this as $e ) {
            $i++;
            if( $i < $from ) continue;
            if( $count >= 0 && $i >= $from + $count ) break;
            if( $p($e) ) $result = $i;
        }
        return $result;
    }
    
    public function mapWithIndex<Tu>((function(Tv, int): Tu) $f): CollectVector<Tu> {
        return new Stream(function() use($f) {
            $i = 0;
            foreach( $this as $e ) {
                yield $f($e, $i);
            }
        });
    }
    
    public function pad(int $length, Tv $elem): CollectVector<Tv> {
        return new Stream(function() use($length, $elem) {
            foreach( $this as $e ) {
                if( $length <= 0 ) break;
                yield $e;
                $length--;
            }
            while( $length > 0 ) {
                yield $elem;
                $length--;
            }
        });
    }
    
    public function prepend(Tv $item): CollectVector<Tv> {
        return new Stream(function() use($item) {
            yield $item;
            foreach( $this as $e ) yield $e;
        });
    }

    public function slice(int $from, int $count = -1): CollectVector<Tv> {
        return new Stream(function() use($from, $count) {
            if( $from >= 0 ) {
                foreach( $this as $e ) {
                    if( $from > 0 ) {
                        $from--;
                        continue;
                    }
                    yield $e;
                    $count--;
                    if( $count === 0 ) break;
                }
            }
        });
    }
    
    public function sort(?(function(Tv,Tv): int) $cmp = null): CollectVector<Tv> {
        throw new \LogicException("Not implemented");
    }

    public function splice(int $from, \Traversable<Tv> $items, int $toRemove = 0): CollectVector<Tv> {
        if( $from < 0 ) return $this;
        
        return new Stream(function() use($from, $items, $toRemove) {
            // Get an iterator for $this
            $iter = $this->getIterator();
            $iter->rewind();
            // Yield items from this until $from
            while( $from > 0 && $iter->valid() ) {
                yield $iter->current();
                $iter->next();
                $from--;
            }
            
            // If from got to 0, yield the items to splice in
            if( $from === 0 ) {
                foreach( $items as $e ) yield $e;
            }
            
            // Skip toRemove items from the iterator
            while( $toRemove > 0 && $iter->valid() ) {
                $iter->next();
                $toRemove--;
            }
            
            // Yield the rest of the items from this
            while( $iter->valid() ) {
                yield $iter->current();
                $iter->next();
            }
        });
    }
    
    public function updated(int $index, Tv $value): CollectVector<Tv> {
        if( $index < 0 ) return $this;
        
        return new Stream(function() use($index, $value) {
            foreach( $this as $e ) {
                yield ($index === 0 ? $value : $e);
                $index--;
            }
        });
    }

    public function zipWithIndex(): CollectVector<\Pair<Tv, int>> {
        return new Stream(function() {
            $i = 0;
            foreach( $this as $e ) {
                yield Pair { $e, $i };
                $i++;
            }
        });
    }
}
