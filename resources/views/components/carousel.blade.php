@props(['autoplay' => true, 'interval' => 5000, 'dark' => false])

<div x-data="{
        active: 0,
        count: 0,
        timer: null,
        touchX: null,
        init() {
            this.count = this.$refs.track.children.length;
            this.play();
        },
        play() {
            if (! {{ $autoplay ? 'true' : 'false' }}) return;
            this.timer = setInterval(() => this.next(), {{ $interval }});
        },
        stop() { clearInterval(this.timer); },
        next() { this.active = (this.active + 1) % this.count; },
        prev() { this.active = (this.active - 1 + this.count) % this.count; },
        goTo(i) { this.active = i; },
     }"
     @mouseenter="stop()" @mouseleave="play()"
     @touchstart="touchX = $event.changedTouches[0].screenX; stop()"
     @touchend="
        let delta = $event.changedTouches[0].screenX - touchX;
        if (delta > 40) prev(); else if (delta < -40) next();
        play();
     "
     class="relative">
    <div class="overflow-hidden rounded-2xl">
        <div x-ref="track" class="flex transition-transform duration-500 ease-out" :style="`transform: translateX(-${active * 100}%)`">
            {{ $slot }}
        </div>
    </div>

    <button type="button" @click="prev()"
            class="absolute left-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full grid place-items-center backdrop-blur transition
                   {{ $dark ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-white/90 hover:bg-white text-gray-900 shadow' }}">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
    </button>
    <button type="button" @click="next()"
            class="absolute right-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full grid place-items-center backdrop-blur transition
                   {{ $dark ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-white/90 hover:bg-white text-gray-900 shadow' }}">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
    </button>

    <div class="flex justify-center gap-1.5 mt-3">
        <template x-for="i in count" :key="i">
            <button type="button" @click="goTo(i - 1)"
                    :class="active === i - 1 ? '{{ $dark ? 'bg-white' : 'bg-brand-800' }} w-6' : '{{ $dark ? 'bg-white/30' : 'bg-gray-300' }} w-2'"
                    class="h-2 rounded-full transition-all"></button>
        </template>
    </div>
</div>
