@foreach($results as $result)
    <article class="member-assistant-result" data-assistant-result>
        <h3 class="member-assistant-result__title">{{ $result['title'] }}</h3>
        <p class="member-assistant-result__answer">{{ $result['answer'] }}</p>
        @if(!empty($result['url']) && !empty($result['route_label']))
            <a href="{{ $result['url'] }}" class="member-assistant-result__link">{{ $result['route_label'] }} →</a>
        @endif
    </article>
@endforeach
