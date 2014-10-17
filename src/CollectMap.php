<?hh // strict

namespace Mkjp\Collect;

use Mkjp\Option\Option;


/**
 * Interface for a Collect map, i.e. an object that maps keys to values
 */
interface CollectMap<Tk, Tv> extends \IteratorAggregate<Tv>, KeyedTraversable<Tk, Tv>, \Countable {
    /**
     * Tests whether this map contains the specified key
     */
    public function containsKey(Tk $key): bool;
    
    /**
     * Tests whether this map contains the given item
     */
    public function containsValue(Tv $item): bool;
    
    /**
     * Returns the number of key-value pairs in the map
     */
    public function count(): int;
    
    /**
     * Returns an iterable containing the entries in this map as key-value pairs
     */
    public function entries(): CollectIterable<\Pair<Tk, Tv>>;
    
    /**
     * Returns the value at the given key
     */
    public function get(Tk $key): Option<Tv>;
    
    /**
     * Returns a keyed iterator for this map, allowing either foreach( ... as $k => $v )
     * or foreach( ... as $v ) syntax to be used
     */
    public function getIterator(): KeyedIterator<Tk, Tv>;

    /**
     * Returns the value at the given key if it exists, or the given default if not
     */
    public function getOrDefault(Tk $key, Tv $default): Tv;
    
    /**
     * Returns an iterable containing the keys in this map
     */
    public function keys(): CollectIterable<Tk>;
    
    /**
     * Returns a new map with the given key removed
     */
    public function remove(Tk $key): CollectMap<Tk, Tv>;
    
    /**
     * Returns a new map with the value at the given key set to the given value
     */
    public function set(Tk $key, Tv $value): CollectMap<Tk, Tv>;
    
    /**
     * Converts this map to a native mutable map
     */
    public function toNativeMap(): \Map<Tk, Tv>;
    
    /**
     * Converts this map to a native immutable map
     */
    public function toNativeImmMap(): \ImmMap<Tk, Tv>;

    /**
     * Returns an iterable containing the values in this map
     */
    public function values(): CollectIterable<Tv>;
}
