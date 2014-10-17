<?hh // strict

namespace Mkjp\Collect\TrieMap;


/**
 * @internal
 * 
 * Class for an index node
 */
final class IndexNode<Tk, Tv> implements Node<Tk, Tv> {
    
    /**
     * Function to do an unsigned right shift
     */
    private static function urshift(int $a, int $b): int {
        // Marked as unsafe since the Hack type checker doesn't recognise PHP_INT_SIZE
        // UNSAFE
        if($b == 0) return $a;
        return ($a >> $b) & ~(1 << (8 * PHP_INT_SIZE - 1) >> ($b - 1));
    }
    
    /**
     * Returns the bit in the hash associated with the given hash
     */
    private static function getBit(int $hash, int $shift): int {
        //$c = (25 - $shift * 5);
        //$b = ((1 << 5) - 1) << $c;
        //return 1 << (($hash & $b) >> $c);
        return 1 << ( IndexNode::urshift($hash, $shift) & 0x01f );
    }
    
    /**
     * Counts the number of set bits in the given integer
     */
    private static function countBits(int $bits): int {
        $c = $bits - (($bits >> 1) & 0x55555555);
        $c = (($c >> 2) & 0x33333333) + ($c & 0x33333333);
        $c = (($c >> 4) + $c) & 0x0F0F0F0F;
        $c = (($c >> 8) + $c) & 0x00FF00FF;
        $c = (($c >> 16) + $c) & 0x0000FFFF;
        return $c;
    }
    
    
    /*** Implementation of Node interface ***/
    
    public function __construct(private int $bitmap,
                                private array<Node<Tk, Tv>> $nodes,
                                private int $count,
                                private int $depth) { }
        
    /**
     * Utility function for constructing an index node from a set of two hash/key/value
     * tuples
     */
    public static function fromItems<Tk1, Tv1>((int, Tk1, Tv1) $item1,
                                               (int, Tk1, Tv1) $item2,
                                               int $depth): IndexNode<Tk1, Tv1> {
        // If the two hashes are fully equal, we can't do much here
        if( $item1[0] === $item2[0] )
            throw new \LogicException("Equal hashes must be handled differently");
                                                  
        // Work out the bit for each item
        $bit1 = IndexNode::getBit($item1[0], $depth * 5);
        $bit2 = IndexNode::getBit($item2[0], $depth * 5);

        // If they are the same bit, we need to create a sub-index node at the next depth
        if( $bit1 === $bit2 ) {
            $subNode = IndexNode::fromItems($item1, $item2, $depth + 1);
            return new IndexNode($bit1, [$subNode], 2, $depth);
        }
        
        // If the bits are different, we create a new index node with appropriate leaf nodes
        $newBitmap = $bit1 | $bit2;
        
        // We know that the indices will be 0 and 1 - we just need to make sure the
        // entries are created in the right order
        $idx1 = IndexNode::countBits($newBitmap & ($bit1 - 1));
        $idx2 = IndexNode::countBits($newBitmap & ($bit2 - 1));
        $node1 = new LeafNode($item1[0], $item1[1], $item1[2], $depth + 1);
        $node2 = new LeafNode($item2[0], $item2[1], $item2[2], $depth + 1);
        if( $idx1 < $idx2 ) {
            $nodes = [$node1, $node2];
        }
        else {
            $nodes = [$node2, $node1];
        }
        return new IndexNode($newBitmap, $nodes, 2, $depth);
    }
    
    public function contains(int $hash, Tk $key): bool {
        $bit = IndexNode::getBit($hash, $this->depth * 5);
        if( ($this->bitmap & $bit) === 0 ) return false;
        
        $idx = IndexNode::countBits($this->bitmap & ($bit - 1));
        return $this->nodes[$idx]->contains($hash, $key);
    }
    
    public function count(): int {
        return $this->count;
    }
    
    public function get(int $hash, Tk $key): ?Tv {
        $bit = IndexNode::getBit($hash, $this->depth * 5);
        if( ($this->bitmap & $bit) === 0 ) return null;
        
        $idx = IndexNode::countBits($this->bitmap & ($bit - 1));
        return $this->nodes[$idx]->get($hash, $key);
    }
    
    public function getIterator(): \Iterator<\Pair<Tk, Tv>> {
        foreach( $this->nodes as $node ) {
            foreach( $node as $kvp ) yield $kvp;
        }
    }
    
    public function remove(int $hash, Tk $key): Node<Tk, Tv> {
        $bit = IndexNode::getBit($hash, $this->depth * 5);
        
        // If we have no entry for the hash, we are done
        if( ($this->bitmap & $bit) === 0 ) return $this;
        
        // Get the current node at the index
        $idx = IndexNode::countBits($this->bitmap & ($bit - 1));
        $current = $this->nodes[$idx];
        // Try removing the key from it
        $newNode = $current->remove($hash, $key);
        
        // If the new node is unchanged, there is nothing to do
        if( $newNode === $current ) return $this;
        
        // If the new node is an empty node, we need to remove it from our array
        if( $newNode->count() === 0 ) {
            // If we only had one node, we are now empty too
            if( count($this->nodes) === 1 ) return new EmptyNode($this->depth);
            
            // Otherwise, create a copy of nodes with the node at idx removed
            $nodes = $this->nodes;
            array_splice($nodes, $idx, 1);
            
            // Update the bitmap
            $newBitmap = ($this->bitmap & ~$bit);
            
            // Return the modified node at the same depth
            return new IndexNode($newBitmap, $nodes, $this->count - 1, $this->depth);
        }
        
        // If the new node is not an empty node, just return a new index node that has
        // the new node at the correct index
        $nodes = $this->nodes;
        $nodes[$idx] = $newNode;
        return new IndexNode($this->bitmap, $nodes, $this->count - 1, $this->depth);
    }
    
    public function set(int $hash, Tk $key, Tv $value): Node<Tk, Tv> {
        $bit = IndexNode::getBit($hash, $this->depth * 5);
        
        // Get the index from the bit count
        // If we have no entry, this is the index at which we will insert one
        // If we have an entry, this is the index at which we will modify
        $idx = IndexNode::countBits($this->bitmap & ($bit - 1));
        
        // If we have no entry for the hash, we need to add one
        if( ($this->bitmap & $bit) === 0 ) {
            $newNode = new LeafNode($hash, $key, $value, $this->depth + 1);
            
            // Insert the new node at $idx in a copy of $this->nodes
            $nodes = $this->nodes;
            array_splice($nodes, $idx, 0, [$newNode]);
            
            // Update the bitmap to reflect that we now have an entry at that bit
            $newBitmap = ($this->bitmap | $bit);
            
            return new IndexNode($newBitmap, $nodes, $this->count + 1, $this->depth);
        }
        
        // If we already have a node at that index, set the key in that node and
        // place the resulting node into the result
        $current = $this->nodes[$idx];
        $newNode = $current->set($hash, $key, $value);
        
        // If the new node is unchanged, return $this to save memory
        if( $newNode === $current ) return $this;
        
        // Otherwise replace the node at $idx in a copy of the nodes
        $nodes = $this->nodes;
        $nodes[$idx] = $newNode;
        return new IndexNode($this->bitmap, $nodes,
                             $this->count - $current->count() + $newNode->count(),
                             $this->depth);
    }
}
