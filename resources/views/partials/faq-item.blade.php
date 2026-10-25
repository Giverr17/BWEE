<details class="faq-item" @if(!empty($open)) open @endif>
  <summary>{{ $faq->question }}</summary>
  <p>{{ $faq->answer }}</p>
</details>
