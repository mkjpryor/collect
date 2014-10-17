<?hh // strict

namespace Mkjp\Collect;


/**
 * Represents the mathematical notion of a set, where membership is determined by
 * the given predicate
 */
final class IntensionalSet<Tv> implements CollectSet<Tv> {
    use SetTrait<Tv>;
    
    public function __construct(private (function(Tv): bool) $predicate) { }
    
    public function contains(Tv $item): bool {
        return call_user_func($this->predicate, $item);
    }
}
