<?hh // strict

namespace Mkjp\Collect;

use Mkjp\Option\Option;


/**
 * Trait implementing the iterable interface using lazy, immutable implementations
 * 
 * Requires using classes to implement getIterator only, although other methods
 * can be overridden for efficiency purposes
 */
trait IterableTrait<Tv> {
    require implements CollectIterable<Tv>;
    
    public function all((function(Tv): bool) $p): bool {
        foreach( $this as $e ) {
            if( !$p($e) ) return false;
        }
        return true;
    }
    
    public function any((function(Tv): bool) $p): bool {
        foreach( $this as $e ) {
            if( $p($e) ) return true;
        }
        return false;
    }
    
    public function collect<Tu>((function(Tv): Tu) $f): CollectIterable<Tu> {
        return new Stream(function() use($f) {
            foreach( $this as $e ) {
                try {
                    yield $f($e);
                }
                catch( \Exception $ex ) {
                    continue;
                }
            }
        });
    }
    
    public function concat(\Traversable<Tv> $other): CollectIterable<Tv> {
        return new Stream(function() use($other) {
            foreach( $this as $e ) yield $e;
            foreach( $other as $e ) yield $e;
        });
    }
    
    public function contains(Tv $item): bool {
        return $this->any($x ==> $x === $item);
    }
    
    public function count(?(function(Tv): bool) $p = null): int {
        // Assume that size provides a more efficient implementation, even if that
        // is just iterating without checking the predicate
        if( $p === null ) return $this->size();
        
        $count = 0;
        foreach( $this as $e ) {
            if( $p($e) ) $count++;
        }
        return $count;
    }
    
    public function distinct(): CollectIterable<Tv> {
        return new Stream(function() {
            // Use a set to store the seen elements
            // TODO: Use a Collect set
            $seen = Set {};
            
            foreach( $this as $e ) {
                if( $seen->contains($e) ) continue;
                $seen->add($e);
                yield $e;
            }
        });
    }
    
    public function filter((function(Tv): bool) $p): CollectIterable<Tv> {
        return new Stream(function() use($p) {
            foreach( $this as $e ) {
                if( $p($e) ) yield $e;
            }
        });
    }
    
    public function first(?(function(Tv): bool) $p = null): Option<Tv> {
        $p = $p ?: $_ ==> true;
        foreach( $this as $e ) {
            if( $p($e) ) return Option::just($e);
        }
        return Option::none();
    }
    
    public function flatMap<Tu>((function(Tv): \Traversable<Tu>) $f): CollectIterable<Tu> {
        return new Stream(function() use($f) {
            foreach( $this as $e ) {
                foreach( $f($e) as $x ) yield $x;
            }
        });
    }
    
    public function foldLeft<Tu>((function(Tu, Tv): Tu) $f, Tu $initial): Tu {
        $result = $initial;
        foreach( $this as $e ) $result = $f($result, $e);
        return $result;
    }
    
    public function foldRight<Tu>((function(Tv, Tu): Tu) $f, Tu $initial): Tu {
        return $this->reverse()->foldLeft(($x, $y) ==> $f($y, $x), $initial);
    }
    
    // TODO: groupBy
    // TODO: grouped
    
    public function isEmpty(): bool {
        // If we enter the body of the iteration, we have at least one item
        foreach( $this as $e ) return false;
        return true;
    }
    
    public function last(?(function(Tv): bool) $p = null): Option<Tv> {
        $p = $p ?: $_ ==> true;
        $last = null;
        foreach( $this as $e ) {
            if( $p($e) ) $last = $e;
        }
        return Option::from($last);
    }
    
    public function map<Tu>((function(Tv): Tu) $f): CollectIterable<Tu> {
        return new Stream(function() use($f) {
            foreach( $this as $e ) yield $f($e);
        });
    }
    
    public function max(?(function(Tv,Tv): int) $cmp = null): Option<Tv> {
        $cmp = $cmp ?: ($x, $y) ==> ($x < $y ? -1 : ($x > $y ? 1 : 0));
        return $this->reduceLeft(($a, $e) ==> $cmp($a, $e) >= 0 ? $a : $e);
    }
    
    public function min(?(function(Tv,Tv): int) $cmp = null): Option<Tv> {
        $cmp = $cmp ?: ($x, $y) ==> ($x < $y ? -1 : ($x > $y ? 1 : 0));
        return $this->reduceLeft(($a, $e) ==> $cmp($a, $e) <= 0 ? $a : $e);
    }
    
    public function partition((function(Tv): bool) $p): \Pair<CollectIterable<Tv>, CollectIterable<Tv>> {
        return Pair { $this->filter($p), $this->filter(($x) ==> !$p($x)) };
    }
    
    public function reduceLeft((function(Tv,Tv): Tv) $f): Option<Tv> {
        $first = true;
        $accum = null;
        foreach( $this as $e ) {
            if( $first ) {
                $accum = $e;
                $first = false;
                continue;
            }
            if( $accum !== null ) $accum = $f($accum, $e);
        }
        return Option::from($accum);
    }

    public function reduceRight((function(Tv,Tv): Tv) $f): Option<Tv> {
        return $this->reverse()->reduceLeft($f);
    }
    
    public function reverse(): CollectIterable<Tv> {
        // Use a Hack vector to do the reverse
        $reversed = $this->toVector();
        $reversed->reverse();
        return new Stream(() ==> $reversed->getIterator());
    }
    
    public function scanLeft<Tu>((function(Tu, Tv): Tu) $f, Tu $initial): CollectIterable<Tu> {
        return new Stream(function() use($f, $initial) {
            $accum = $initial;
            yield $accum;
            foreach( $this as $e ) {
                $accum = $f($accum, $e);
                yield $accum;
            }
        });
    }
    
    public function scanRight<Tu>((function(Tv, Tu): Tu) $f, Tu $initial): CollectIterable<Tu> {
        return $this->reverse()->scanLeft(($x, $y) ==> $f($y, $x), $initial);
    }
    
    public function size(): int {
        $count = 0;
        foreach( $this as $_ ) $count++;
        return $count;
    }
    
    public function skip(int $n): CollectIterable<Tv> {
        return new Stream(function() use($n) {
            $i = 0;
            foreach( $this as $e ) {
                if( $i >= $n ) yield $e;
                $i++;
            }
        });
    }
    
    public function skipWhile((function(Tv): bool) $p): CollectIterable<Tv> {
        return new Stream(function() use($p) {
            $yield = false;
            foreach( $this as $e ) {
                $yield = $yield || !$p($e);
                if( $yield ) yield $e;
            }
        });
    }
    
    public function span((function(Tv): bool) $p): \Pair<CollectIterable<Tv>, CollectIterable<Tv>> {
        return Pair { $this->takeWhile($p), $this->skipWhile($p) };
    }
    
    public function take(int $n): CollectIterable<Tv> {
        return new Stream(function() use($n) {
            $i = 0;
            foreach( $this as $e ) {
                if( $i >= $n ) break;
                yield $e;
                $i++;
            }
        });
    }
    
    public function takeWhile((function(Tv): bool) $p): CollectIterable<Tv> {
        return new Stream(function() use($p) {
            foreach( $this as $e ) {
                if( !$p($e) ) break;
                yield $e;
            }
        });
    }
    
    public function tap((function(Tv): void) $f): CollectIterable<Tv> {
        foreach( $this as $e ) $f($e);
        return $this;
    }
    
    public function toArray(): array<Tv> {
        $result = array();
        foreach( $this as $e ) $result[] = $e;
        return $result;
    }
    
    public function toNativeImmMap<Tk, Tu>((function(Tv): \Pair<Tk, Tu>) $f): \ImmMap<Tk, Tu> {
        return \ImmMap::fromItems($this->map($f));
    }
    
    public function toNativeImmSet(): \ImmSet<Tv> {
        return new \ImmSet($this);
    }
    
    public function toNativeImmVector(): \ImmVector<Tv> {
        return new \ImmVector($this);
    }
    
    public function toNativeMap<Tk, Tu>((function(Tv): \Pair<Tk, Tu>) $f): \Map<Tk, Tu> {
        return \Map::fromItems($this->map($f));
    }
    
    public function toNativeSet(): \Set<Tv> {
        return new \Set($this);
    }
    
    public function toNativeVector(): \Vector<Tv> {
        return new \Vector($this);
    }
    
    public function toMap<Tk, Tu>((function(Tv): \Pair<Tk, Tu>) $f): CollectMap<Tk, Tu> {
        throw new \LogicException("Not implemented");
    }
    
    public function toSet(): ExtensionalSet<Tv> {
        throw new \LogicException("Not implemented");
    }
    
    public function toVector(): CollectVector<Tv> {
        return new Stream(() ==> $this->getIterator());
    }
    
    public function zip<Tu>(\Traversable<Tu> $other): CollectIterable<\Pair<Tv, Tu>> {
        return new Stream(function() use($other) {
            // Get an iterator for this iterable so we can step the sequences
            $iter = $this->getIterator();
            foreach( $other as $e ) {
                if( !$iter->valid() ) break;
                yield Pair { $iter->current(), $e };
                $iter->next();
            }
        });
    }
}
