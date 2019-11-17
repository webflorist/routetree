<url>
    @foreach ($urlData as $tag => $value)
        <{{$tag}}>{{$value}}</{{$tag}}>
    @endforeach
</url>