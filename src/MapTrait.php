<?hh // strict

namespace Mkjp\Collect;

use Mkjp\Option\Option;


/**
 * Trait implementing the map interface using immutable implementations
 * 
 * Requires implementing classes to implement containsKey, getIterator, get, set and remove
 */
trait MapTrait<Tk, Tv> {
    require implements CollectMap<Tk, Tv>;
    
    
    public function containsValue(Tv $item): bool {
        return $this->values()->contains($item);
    }
    
    public function count(): int {
        return $this->keys()->count();
    }
    
    public function entries(): CollectIterable<\Pair<Tk, Tv>> {
        return new Stream(function() {
            foreach( $this as $k => $v ) yield Pair { $k, $v };
        });
    }
    
    public function getOrDefault(Tk $key, Tv $default): Tv {
        return $this->get($key)->getOrDefault($default);
    }
    
    public function keys(): CollectIterable<Tk> {
        return new Stream(function() {
            foreach( $this as $k => $_ ) yield $k;
        });
    }
    
    public function toNativeMap(): \Map<Tk, Tv> {
        return new \Map($this);
    }
    
    public function toNativeImmMap(): \ImmMap<Tk, Tv> {
        return new \ImmMap($this);
    }

    public function values(): CollectIterable<Tv> {
        return new Stream(function() {
            foreach( $this as $v ) yield $v;
        });
    }
}
