<?hh // strict

namespace Mkjp\Collect;


/**
 * Lazy, immutable vector
 * 
 * Takes an 'iterator factory' (normally a generator-producing function) as its
 * only argument
 */
final class Stream<Tv> implements CollectVector<Tv> {
    use VectorTrait<Tv>;
    
    /**
     * Create a new Stream that uses the given 'iterator factory' to produce iterators
     */
    public function __construct(private (function(): \Iterator<Tv>) $newIterator) {}
    
    public function getIterator(): \Iterator<Tv> {
        return call_user_func($this->newIterator);
    }
}
