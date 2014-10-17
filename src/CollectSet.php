<?hh // strict

namespace Mkjp\Collect;


/**
 * Interface for a set (in the mathematical sense)
 * 
 * NOTE: This interface represents a set in the mathematical sense, i.e. membership
 *       is defined by the implementation of contains, and as such does not allow
 *       the elements to be iterated over
 *       If you require a set that can be iterated over, see ExtensionalSet
 */
interface CollectSet<Tv> {
    /**
     * Returns true if this set contains the item, false otherwise
     */
    public function contains(Tv $item): bool;
    
    /**
     * Returns a new set representing the difference between this set and the
     * given set, i.e. a set containing the items in this set that are not in $other
     */
    public function diff(CollectSet<Tv> $other): CollectSet<Tv>;
    
    /**
     * Returns a new set representing the intersection of this set and the given
     * set, i.e. a set containing the items that are in both sets
     */
    public function intersect(CollectSet<Tv> $other): CollectSet<Tv>;

    /**
     * Returns a new set representing the union of this set and the given set, i.e.
     * a set containing all the items from both sets
     *
     * @param Set $other
     * @return static
     */
    public function union(CollectSet<Tv> $other): CollectSet<Tv>;
}
