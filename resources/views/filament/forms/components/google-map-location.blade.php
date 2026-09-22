<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $apiKey = $getApiKey();
        $statePath = $getStatePath();
        $addressPath = $getAddressStatePath();
        $lat = $getLatitude();
        $lng = $getLongitude();
        $isDisabled = $isDisabled();
    @endphp

    <div
        class="fi-fo-google-map relative overflow-hidden rounded-xl ring-1 ring-gray-950/10 dark:ring-white/20"
        x-data="{
            map: null,
            marker: null,
            apiKey: @js($apiKey),
            lat: @js($lat),
            lng: @js($lng),
            disabled: @js($isDisabled),
            statePath: @js($statePath),
            addressPath: @js($addressPath),
            async init() {
                await this.$nextTick()
                if (this.apiKey) {
                    await this.bootGoogle()
                    return
                }
                this.bootLeaflet()
            },
            async bootGoogle() {
                await this.loadGoogle()
                const position = { lat: Number(this.lat), lng: Number(this.lng) }
                this.map = new google.maps.Map(this.$refs.canvas, {
                    center: position,
                    zoom: 14,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                    zoomControl: true,
                })
                this.marker = new google.maps.Marker({
                    map: this.map,
                    position,
                    draggable: ! this.disabled,
                })
                this.$nextTick(() => google.maps.event.trigger(this.map, 'resize'))
                if (this.disabled) {
                    return
                }
                this.marker.addListener('dragend', () => this.syncGoogle())
                this.map.addListener('click', (event) => {
                    this.marker.setPosition(event.latLng)
                    this.syncGoogle()
                })
                if (this.$refs.search && google.maps.places) {
                    const autocomplete = new google.maps.places.Autocomplete(this.$refs.search, {
                        fields: ['geometry', 'formatted_address'],
                    })
                    autocomplete.bindTo('bounds', this.map)
                    autocomplete.addListener('place_changed', () => {
                        const place = autocomplete.getPlace()
                        const location = place.geometry?.location
                        if (! location) {
                            return
                        }
                        this.map.setCenter(location)
                        this.map.setZoom(16)
                        this.marker.setPosition(location)
                        this.persist(location.lat(), location.lng(), place.formatted_address ?? null)
                    })
                }
            },
            syncGoogle() {
                const position = this.marker.getPosition()
                const lat = position.lat()
                const lng = position.lng()
                const geocoder = new google.maps.Geocoder()
                geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                    const address = status === 'OK' ? (results?.[0]?.formatted_address ?? null) : null
                    this.persist(lat, lng, address)
                })
            },
            async bootLeaflet() {
                await this.loadLeaflet()
                this.map = L.map(this.$refs.canvas).setView([Number(this.lat), Number(this.lng)], 14)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                }).addTo(this.map)
                this.marker = L.marker([Number(this.lat), Number(this.lng)], { draggable: ! this.disabled }).addTo(this.map)
                this.$nextTick(() => this.map.invalidateSize())
                if (this.disabled) {
                    return
                }
                this.marker.on('dragend', () => {
                    const pos = this.marker.getLatLng()
                    this.persist(pos.lat, pos.lng, null)
                })
                this.map.on('click', (event) => {
                    this.marker.setLatLng(event.latlng)
                    this.persist(event.latlng.lat, event.latlng.lng, null)
                })
            },
            persist(lat, lng, address) {
                this.lat = lat
                this.lng = lng
                this.$wire.set(`${this.statePath}.lat`, lat)
                this.$wire.set(`${this.statePath}.lng`, lng)
                if (address) {
                    this.$wire.set(this.addressPath, address)
                }
            },
            loadGoogle() {
                if (window.google?.maps) {
                    return Promise.resolve()
                }
                if (window.__sakinaGoogleMapsLoader) {
                    return window.__sakinaGoogleMapsLoader
                }
                window.__sakinaGoogleMapsLoader = new Promise((resolve, reject) => {
                    window.__sakinaGoogleMapsInit = () => resolve()
                    const script = document.createElement('script')
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(this.apiKey)}&libraries=places&callback=__sakinaGoogleMapsInit`
                    script.async = true
                    script.onerror = reject
                    document.head.appendChild(script)
                })
                return window.__sakinaGoogleMapsLoader
            },
            loadLeaflet() {
                if (window.L) {
                    return Promise.resolve()
                }
                if (window.__sakinaLeafletLoader) {
                    return window.__sakinaLeafletLoader
                }
                window.__sakinaLeafletLoader = new Promise((resolve, reject) => {
                    const css = document.createElement('link')
                    css.rel = 'stylesheet'
                    css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
                    document.head.appendChild(css)
                    const script = document.createElement('script')
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
                    script.onload = () => resolve()
                    script.onerror = reject
                    document.head.appendChild(script)
                })
                return window.__sakinaLeafletLoader
            },
        }"
    >
        @if (filled($apiKey))
            <input
                type="text"
                x-ref="search"
                @disabled($isDisabled)
                placeholder="{{ __('settings.fields.map_search') }}"
                class="fi-fo-google-map-search absolute z-10 m-3 w-[min(100%-1.5rem,20rem)] rounded-lg border-none bg-white px-3 py-2 text-sm text-gray-950 shadow-md ring-1 ring-gray-950/10 outline-none placeholder:text-gray-400 dark:bg-gray-900 dark:text-white dark:ring-white/20"
            />
        @endif
        <div
            x-ref="canvas"
            class="fi-fo-google-map-canvas relative w-full bg-gray-100 dark:bg-gray-800"
            wire:ignore
        ></div>
        @if (blank($apiKey))
            <p class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                {{ __('settings.helpers.map_missing_key') }}
            </p>
        @endif
    </div>
</x-dynamic-component>
