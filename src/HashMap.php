<?hh // strict

namespace Mkjp\Collect;

use Mkjp\Option\Option;


/**
 * @internal
 * 
 * Class used for entries in the hash map
 * They use a linked-list type structure to link entries with the same hash
 */
final class HashMapEntry<Tk, Tv> {
    public function __construct(private Tk $key, private Tv $value,
                                private ?HashMapEntry<Tk, Tv> $next = null) {}
                                
    public function key(): Tk {
        return $this->key;
    }
    
    public function next(): ?HashMapEntry<Tk, Tv> {
        return $this->next;
    }
    
    public function value(): Tv {
        return $this->value;
    }
}


/**
 * Immutable implementation of the map interface using a hash-map type construct
 * 
 * Entries are stored as a table of string hash => key-value pair using a native Map
 * 
 * Immutability is acheived by copying the table on modification (so not massively
 * memory efficient if references to the previous state are maintained, and pretty slow)
 * 
 * However, containsKey and get should have good performance
 */
final class HashMap<Tk, Tv> implements CollectMap<Tk, Tv> {
    use MapTrait<Tk, Tv>;
    
    /**
     * Creates a new HashMap with the given entry table
     * We also pass the size, since we can track that easily
     */
    private function __construct(private Map<string, HashMapEntry<Tk, Tv>> $table,
                                 private int $size) {}
                                 
    private static function hashCode(Tk $key): string {
        // Get a string hash for the value
        if( is_object($key) ) {
            // For objects, use spl_object_hash
            return spl_object_hash($key);
        }
        else {
            // Now we know we have a primitive type
    
            // For arrays, first hash the contents
            if( is_array($key) )
                $key = array_map($x ==> HashMap::hashCode($x), $key);
    
            // Just cast the object to a string
            // We don't worry too much about collisions here, since they should be
            // few in number, and we can deal with them if they happen
            // Use md5 to restrict the size of the returned string
            return md5((string)$key);
        }
    }

    public function containsKey(Tk $key): bool {
        $hash = HashMap::hashCode($key);
        $entry = $this->table->get($hash); // This will be null if the hash doesn't exist
        while( $entry !== null ) {
            if( $key === $entry->key() ) return true;
            $entry = $entry->next();
        }
        return false;
    }
    
    public function count(): int {
        return $this->size;
    }
    
    public function get(Tk $key): Option<Tv> {
        $hash = HashMap::hashCode($key);
        $entry = $this->table->get($hash); // This will be null if the hash doesn't exist
        while( $entry !== null ) {
            if( $key === $entry->key() ) {
                return Option::just($entry->value());
            }
            $entry = $entry->next();
        }
        return Option::none();
    }
    
    public function getIterator(): KeyedIterator<Tk, Tv> {
        // Create a KeyedIterator from an iterator over our key-value pairs
        return new KeyValuePairIterator(call_user_func(function() {
            foreach( $this->table as $entry ) {
                while( $entry !== null ) {
                    yield Pair { $entry->key(), $entry->value() };
                    $entry = $entry->next();
                }
            }
        }));
    }

    /**
     * Returns a new map with the given key removed
     */
    public function remove(Tk $key): CollectMap<Tk, Tv> {
        $hash = HashMap::hashCode($key);
        
        // Build a new entry without the given key, if it exists
        // We reuse the tail of the old entry to save as much memory as we can
        $foundKey = false;
        $prev = [];
        $entry = $this->table->get($hash); // This will be null if the hash doesn't exist
        $next = null;
        while( $entry !== null ) {
            $next = $entry->next();
            // If we get to the entry where the keys are equal, we are done
            if( $key === $entry->key() ) {
                $foundKey = true;
                break;
            }
            $prev[] = $entry;
            $entry = $next;
        }
        $newEntry = $next;
        foreach( $prev as $e )
            $newEntry = new HashMapEntry($e->key(), $e->value(), $newEntry);
        
        if( $foundKey ) {
            // If the key was found, insert the new entry or delete the current one
            // from a clone of the table
            $table = clone $this->table;
            if( $newEntry !== null ) {
                $table[$hash] = $newEntry;
            }
            else {
                $table->removeKey($hash);
            }
            return new HashMap($table, $this->size--);
        }
        else {
            // If no changes were required, just return $this
            return $this;
        }
    }
    
    /**
     * Returns a new map with the value at the given key set to the given value
     */
    public function set(Tk $key, Tv $value): CollectMap<Tk, Tv> {
        $hash = HashMap::hashCode($key);
        
        // Build the new entry
        $newEntry = null;
        $isInsertion = true; // Indicates if the new entry represents an insertion or an update
        $prev = [];
        $entry = $this->table->get($hash); // This will be null if the key doesn't exist
        $next = null;
        while( $entry !== null ) {
            $next = $entry->next();
            // If we get to the entry where the keys are equal, we are done
            if( $key === $entry->key() ) {
                $isInsertion = false;
                break;
            }
            $prev[] = $entry;
            $entry = $next;
        }
        $newEntry = new HashMapEntry($key, $value, $next);
        foreach( $prev as $e )
            $newEntry = new HashMapEntry($e->key(), $e->value(), $newEntry);
        
        // Copy the table and insert the new entry before returning a new map
        $table = clone $this->table;
        $table[$hash] = $newEntry;
        $newSize = $isInsertion ? $this->size + 1 : $this->size;
        return new HashMap($table, $newSize);
    }
    
    /**
     * Create a new empty HashMap
     */
    public static function create<Tk1, Tv1>(): CollectMap<Tk1, Tv1> {
        return new HashMap(Map {}, 0);
    }
    
    /**
     * Create a new HashMap containing the given items
     */
    public static function from<Tk1, Tv1>(KeyedTraversable<Tk1, Tv1> $items): CollectMap<Tk1, Tv1> {
        $map = HashMap::create();
        foreach( $items as $k => $v ) $map = $map->set($k, $v);
        return $map;
    }
}
