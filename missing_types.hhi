<?hh // decl


class EmptyIterator<Tv> implements Iterator<Tv> {
    public function current(): Tv { }
    public function key(): mixed { }
    public function next(): void { }
    public function rewind(): void { }
    public function valid(): bool { }
}
