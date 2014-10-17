<?hh // strict

namespace Mkjp\Collect;

use Mkjp\Option\Option;


/**
 * Interface for a vector, i.e. an ordered iterable with access by integer index
 */
interface CollectVector<Tv> extends CollectIterable<Tv> {
    /**
     * Returns a new vector with the given item appended
     */
    public function append(Tv $item): CollectVector<Tv>;
    
    /**
     * Gets the value at the given index
     */
    public function at(int $index): Option<Tv>;
    
    /**
     * Similar to filter, except that the given function will receive the index as the
     * second argument
     */
    public function filterWithIndex((function(Tv, int): bool) $p): CollectVector<Tv>;
    
    /**
     * Returns the index of the first occurence of the given object in the vector,
     * or -1 if the object does not occur in the vector
     * 
     * If $from is given, only elements with an index >= $from are considered
     * 
     * If $count is given, a maximum of $count elements starting at $from are considered
     * 
     * WARNING: May not return for infinite vectors
     */
    public function indexOf(Tv $item, int $from = 0, int $count = -1): int;
    
    /**
     * Returns the first index where the given predicate returns true, or -1 if
     * the predicate is false everywhere
     * 
     * If $from is given, only elements with an index >= $from are considered
     * 
     * If $count is given, a maximum of $count elements starting at $from are considered
     * 
     * WARNING: May not return for infinite vectors
     */
    public function indexWhere((function(Tv): bool) $p, int $from = 0, int $count = -1): int;
    
    /**
     * Returns the index of the last occurence of the given object in the vector,
     * or -1 if the object does not occur in the vector
     * 
     * If $from is given, only elements with an index >= $from are considered
     * 
     * If $count is given, a maximum of $count elements starting at $from are considered
     * 
     * WARNING: Will not return for infinite vectors
     */
    public function lastIndexOf(Tv $item, int $from = 0, int $count = -1): int;

    /**
     * Returns the last index where the given predicate returns true, or -1 if
     * the predicate is false everywhere
     * 
     * If $from is given, only elements with an index >= $from are considered
     * 
     * If $count is given, a maximum of $count elements starting at $from are considered
     * 
     * WARNING: Will not return for infinite vectors
     */
    public function lastIndexWhere((function(Tv): bool) $p, int $from = 0, int $count = -1): int;
    
    /**
     * Similar to map, except that the given function will receive the index as the
     * second argument
     */
    public function mapWithIndex<Tu>((function(Tv, int): Tu) $f): CollectVector<Tu>;
    
    /**
     * Returns a new vector with the given element appended until the target length
     * is reached
     * 
     * If $length is less than the size of this vector, it is truncated
     */
    public function pad(int $length, Tv $elem): CollectVector<Tv>;
    
    /**
     * Returns a new vector with the given item prepended
     */
    public function prepend(Tv $item): CollectVector<Tv>;

    /**
     * Returns a new vector representing the portion of this vector starting at
     * $from
     * 
     * If $count is given, a maximum of $count elements from $from are returned
     * 
     * If $from is out of bounds, an empty vector is returned
     */
    public function slice(int $from, int $count = -1): CollectVector<Tv>;
    
    /**
     * Sorts this vector according to the given ordering function
     * 
     * If no ordering function is given, elements are compared using < and >
     *
     * NOTE: This is different from explicitly creating a sorted type whose elements
     *       are automatically sorted on addition
     * 
     * WARNING: Will not return for infinite vectors
     */
    public function sort(?(function(Tv,Tv): int) $cmp = null): CollectVector<Tv>;

    /**
     * Returns a new vector where $toRemove elements are replaced with the values
     * from $items at index $from
     * 
     * If $from is out of range, this vector is returned
     */
    public function splice(int $from, \Traversable<Tv> $items, int $toRemove = 0): CollectVector<Tv>;
    
    /**
     * Returns a new vector with the value at the given index replaced with the
     * given value
     * 
     * If $index is out of range, the vector is unchanged
     */
    public function updated(int $index, Tv $value): CollectVector<Tv>;

    /**
     * Returns a vector of pairs that combines the elements of this vector with
     * their indices
     */
    public function zipWithIndex(): CollectVector<\Pair<Tv, int>>;
}
