<?hh // strict

namespace Mkjp\Collect;

use Mkjp\Option\Option;


/**
 * Interface for a Collect iterable
 */
interface CollectIterable<Tv> extends \IteratorAggregate<Tv>, \Countable {
    /**
     * Tests if the given predicate holds for all elements of the iterable
     * 
     * WARNING: May not return for infinite iterables
     */
    public function all((function(Tv): bool) $p): bool;
    
    /**
     * Tests if the given predicate holds for any element of the iterable
     * 
     * WARNING: May not return for infinite iterables
     */
    public function any((function(Tv): bool) $p): bool;
    
    /**
     * Similar to map except that values for which $f throws an exception are skipped
     */
    public function collect<Tu>((function(Tv): Tu) $f): CollectIterable<Tu>;
    
    /**
     * Returns an iterable containing the elements of this iterable followed by
     * the elements of $other
     * 
     * WARNING: May return different results on each run for unordered iterables
     */
    public function concat(\Traversable<Tv> $other): CollectIterable<Tv>;
    
    /**
     * Tests whether this iterable contains the given item
     * 
     * WARNING: May not return for infinite iterables
     */
    public function contains(Tv $item): bool;
    
    /**
     * Returns the number of elements that satisfy the given predicate
     * 
     * If no predicate is given, returns the number of elements in the iterable
     * 
     * WARNING: Will not return for infinite iterables
     */
    public function count(?(function(Tv): bool) $p = null): int;
    
    /**
     * Returns an iterable containing only the distinct elements from this iterable
     */
    public function distinct(): CollectIterable<Tv>;
    
    /**
     * Returns an iterable containing all the elements of this iterable that
     * satisfy the given predicate
     */
    public function filter((function(Tv): bool) $p): CollectIterable<Tv>;
    
    /**
     * Finds the first element of this iterable that satisfies the given predicate
     * 
     * If no predicate is given, returns the first element of this iterable
     * 
     * WARNINGS: May not terminate for infinite iterables
     *           May return different results on each run for unordered iterables
     */
    public function first(?(function(Tv): bool) $p = null): Option<Tv>;
    
    /**
     * Returns an iterable whose elements are the elements of the traversables
     * obtained from applying $f to the elements of $this
     */
    public function flatMap<Tu>((function(Tv): \Traversable<Tu>) $f): CollectIterable<Tu>;
    
    /**
     * Returns the result of applying $f to a start value then all elements of this
     * iterable, going left to right
     * 
     * WARNINGS: Will not terminate for infinite iterables
     *           May return different results on each run for unordered iterables
     *           if $f is not associative and commutative
     */
    public function foldLeft<Tu>((function(Tu, Tv): Tu) $f, Tu $initial): Tu;
    
    /**
     * Returns the result of applying $f to a start value then all elements of this
     * iterable, going right to left
     * 
     * WARNINGS: Will not terminate for infinite iterables
     *           May return different results on each run for unordered iterables
     *           if $f is not associative and commutative
     */
    public function foldRight<Tu>((function(Tv, Tu): Tu) $f, Tu $initial): Tu;
    
    /**
     * Partitions this iterable into a map of iterables using the given function
     * to produce keys from the elements
     */
    // TODO: public function groupBy<Tk>((function(Tv): Tk) $f): CollectMap<Tk, CollectIterable<Tv>>;

    /**
     * Returns an iterator that consists of fixed size chunks of this iterable
     *
     * The iterator can only be assumed to be good for a single iteration (i.e.
     * it cannot be rewound)
     */
    // TODO: public function grouped(int $n): \Iterator<CollectIterable<Tv>>;
    
    /**
     * Returns true if this iterable is empty, false otherwise
     */
    public function isEmpty(): bool;
    
    /**
     * Finds the last element of this iterable that satisfies the given predicate
     * 
     * If no predicate is given, returns the last element of the iterable
     * 
     * WARNINGS: Will not terminate for infinite iterables
     *           May return different results on each run for unordered iterables
     */
    public function last(?(function(Tv): bool) $p = null): Option<Tv>;
    
    /**
     * Applies $f to the elements of this iterable and returns the resulting iterable
     */
    public function map<Tu>((function(Tv): Tu) $f): CollectIterable<Tu>;
    
    /**
     * Returns the maximum element in this iterable
     * 
     * Optionally, a comparison function can be given
     * The comparison function must return an integer less than, equal to, or greater
     * than zero if the first argument is considered to be respectively less than,
     * equal to, or greater than the second
     */
    public function max(?(function(Tv,Tv): int) $cmp = null): Option<Tv>;
    
    /**
     * Returns the minimum element in this iterable
     * 
     * Optionally, a comparison function can be given
     * The comparison function must return an integer less than, equal to, or greater
     * than zero if the first argument is considered to be respectively less than,
     * equal to, or greater than the second
     */
    public function min(?(function(Tv,Tv): int) $cmp = null): Option<Tv>;
    
    /**
     * Partitions this iterable into two iterables depending on the given predicate
     */
    public function partition((function(Tv): bool) $p): \Pair<CollectIterable<Tv>, CollectIterable<Tv>>;
    
    /**
     * Applies the binary function $f to the elements of this iterable, going left
     * to right
     * 
     * If the iterable is empty, an empty Option is returned
     * 
     * WARNINGS: Will not terminate for infinite iterables
     *           May return different results on each run for unordered iterables
     *           if $f is not associative and commutative
     */
    public function reduceLeft((function(Tv,Tv): Tv) $f): Option<Tv>;

    /**
     * Applies the binary function $f to the elements of this iterable, going right
     * to left
     * 
     * If the iterable is empty, an empty Option is returned
     * 
     * WARNINGS: Will not terminate for infinite iterables
     *           May return different results on each run for unordered iterables
     *           if $f is not associative and commutative
     */
    public function reduceRight((function(Tv,Tv): Tv) $f): Option<Tv>;
    
    /**
     * Returns an iterable containing the elements of this iterable in reverse
     * 
     * WARNINGS: Will not terminate for infinite iterables
     *           May return different results on each run for unordered iterables
     */
    public function reverse(): CollectIterable<Tv>;
    
    /**
     * Produces an iterable containing the cumulative results of applying $f
     * from left to right with start value $initial
     * 
     * WARNING: May return different results on each run for unordered iterables
     *          if $f is not associative and commutative
     */
    public function scanLeft<Tu>((function(Tu, Tv): Tu) $f, Tu $initial): CollectIterable<Tu>;
    
    /**
     * Produces an iterable containing the cumulative results of applying $f
     * from right to left with start value $initial
     * 
     * WARNING: May return different results on each run for unordered iterables
     *          if $f is not associative and commutative
     */
    public function scanRight<Tu>((function(Tv, Tu): Tu) $f, Tu $initial): CollectIterable<Tu>;
    
    /**
     * Returns the number of elements in the iterable
     */
    public function size(): int;
    
    /**
     * Drops the first $n elements from this iterable
     * 
     * WARNING: May return different results on each run for unordered iterables
     */
    public function skip(int $n): CollectIterable<Tv>;
    
    /**
     * Drops the longest prefix that satisfies the predicate from this iterable
     * 
     * WARNING: May return different results on each run for unordered iterables
     */
    public function skipWhile((function(Tv): bool) $p): CollectIterable<Tv>;
    
    /**
     * Returns an iterator that consists of sliding windows of the given size over
     * this iterable, with step elements between starting a new window
     * 
     * The iterator can only be assumed to be good for a single iteration (i.e.
     * it cannot be rewound)
     */
    // TODO: public function sliding(int $size, int $step = 1): \Iterator<CollectIterable<Tv>>;
    
    /**
     * Splits this iterable into a prefix/suffix pair according to the predicate
     */
    public function span((function(Tv): bool) $p): \Pair<CollectIterable<Tv>, CollectIterable<Tv>>;
    
    /**
     * Returns an iterable consisting of the first $n elements of this iterable
     * 
     * WARNING: May return different results on each run for unordered iterables
     */
    public function take(int $n): CollectIterable<Tv>;
    
    /**
     * Returns the longest prefix of this iterable for which the predicate returns true
     * 
     * WARNING: May return different results on each run for unordered iterables
     */
    public function takeWhile((function(Tv): bool) $p): CollectIterable<Tv>;
    
    /**
     * Executes $f for each element of this iterable before returning $this
     * 
     * Useful for inspecting values mid-chain
     * 
     * WARNING: Will not terminate for infinite iterables
     */
    public function tap((function(Tv): void) $f): CollectIterable<Tv>;
    
    /**
     * Converts this iterable to a native array
     */
    public function toArray(): array<Tv>;
    
    /**
     * Converts this iterable to a native immutable map
     * 
     * The given function is used to convert the elements of the iterable to
     * key-value pairs for the map
     */
    public function toNativeImmMap<Tk, Tu>((function(Tv): \Pair<Tk, Tu>) $f): \ImmMap<Tk, Tu>;
    
    /**
     * Converts this iterable to a native immutable set
     */
    public function toNativeImmSet(): \ImmSet<Tv>;
    
    /**
     * Converts this iterable to a native immutable vector
     */
    public function toNativeImmVector(): \ImmVector<Tv>;
    
    /**
     * Converts this iterable to a native mutable map
     * 
     * The given function is used to convert the elements of the iterable to
     * key-value pairs for the map
     */
    public function toNativeMap<Tk, Tu>((function(Tv): \Pair<Tk, Tu>) $f): \Map<Tk, Tu>;
    
    /**
     * Converts this iterable to a native mutable set
     */
    public function toNativeSet(): \Set<Tv>;
    
    /**
     * Converts this iterable to a native mutable vector
     */
    public function toNativeVector(): \Vector<Tv>;
    
    /**
     * Converts this iterable to a Collect map
     * 
     * The given function is used to convert the elements of the iterable to
     * key-value pairs for the map
     */
    public function toMap<Tk, Tu>((function(Tv): \Pair<Tk, Tu>) $f): CollectMap<Tk, Tu>;
    
    /**
     * Converts this iterable to a Collect set
     */
    public function toSet(): ExtensionalSet<Tv>;
    
    /**
     * Converts this iterable to a Collect vector
     */
    public function toVector(): CollectVector<Tv>;
    
    /**
     * Returns an iterable that combines the elements of this iterable and another
     * traversable, pair-wise
     */
    public function zip<Tu>(\Traversable<Tu> $other): CollectIterable<\Pair<Tv, Tu>>;
}
