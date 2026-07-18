<span {{ $attributes->merge(['class' => 'user-context-presence']) }} data-online="{{ $online ? 'true' : 'false' }}">
    <span class="user-context-presence__dot" style="display:inline-block;width:.6em;height:.6em;border-radius:50%;background:{{ $online ? '#22c55e' : '#9ca3af' }};"></span>
    <span class="user-context-presence__label">{{ $online ? __('user-context::user-context.online') : __('user-context::user-context.offline') }}</span>
</span>
