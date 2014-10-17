<?hh // strict

namespace Mkjp\Collect;


/**
 * Iterator that takes another iterator of key-value pairs and turns it into a
 * keyed iterator
 */
class KeyValuePairIterator<Tk, Tv> implements KeyedIterator<Tk, Tv> {
    /**
     * Construct a new KeyValuePairIterator from the given iterator providing
     * key-value pair objects
     */
    public function __construct(private \Iterator<\Pair<Tk, Tv>> $iter) {}
    
    public function current(): Tv {
        return $this->iter->current()[1];
    }
    
    public function key(): Tk {
        return $this->iter->current()[0];
    }
    
    public function next(): void {
        $this->iter->next();
    }
    
    public function rewind(): void {
        $this->iter->rewind();
    }
    
    public function valid(): bool {
        return $this->iter->valid();
    }
}
