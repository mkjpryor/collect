<?hh // strict

namespace Mkjp\Collect;


/**
 * Trait implementing the set interface
 * 
 * Requires using classes to implement contains
 */
trait SetTrait<Tv> {
    require implements CollectSet<Tv>;
    
    public function diff(CollectSet<Tv> $other): CollectSet<Tv> {
        return new IntensionalSet($x ==> ($this->contains($x) && !$other->contains($x)));
    }
    
    public function intersect(CollectSet<Tv> $other): CollectSet<Tv> {
        return new IntensionalSet($x ==> ($this->contains($x) && $other->contains($x)));
    }

    public function union(CollectSet<Tv> $other): CollectSet<Tv> {
        return new IntensionalSet($x ==> ($this->contains($x) || $other->contains($x)));
    }
}
