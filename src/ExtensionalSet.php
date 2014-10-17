<?hh // strict

namespace Mkjp\Collect;


/**
 * Interface for a set where membership is defined by presence in a collection, and
 * the elements of the set can be iterated over
 */
interface ExtensionalSet<Tv> extends CollectSet<Tv>, CollectIterable<Tv> {
    /**
     * Returns a new set with the given item added
     */
    public function add(Tv $item): ExtensionalSet<Tv>;
    
    /**
     * Returns a new set with the given item removed
     */
    public function remove(Tv $item): ExtensionalSet<Tv>;
}
