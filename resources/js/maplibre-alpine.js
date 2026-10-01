import * as maplibregl from 'maplibre-gl';

export default () => ({
    lat: 0,
    lng: 0,
    zoom: 10,
    markerTitle: '',
    markersData: [],
    map: null,
    
    initMap({ lat, lng, zoom, markerTitle, markers }) {
        this.lat = lat || 0;
        this.lng = lng || 0;
        this.zoom = zoom || 10;
        this.markerTitle = markerTitle;
        this.markersData = markers || [];

        // If we have an array of markers, compute center from the first marker if lat/lng are 0
        if (this.markersData.length > 0 && this.lat === 0 && this.lng === 0) {
            this.lat = this.markersData[0].lat;
            this.lng = this.markersData[0].lng;
        }

        this.map = new maplibregl.Map({
            container: this.$refs.mapContainer,
            style: {
                version: 8,
                sources: {
                    osm: {
                        type: 'raster',
                        tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                        tileSize: 256,
                        attribution: '&copy; OpenStreetMap'
                    }
                },
                layers: [
                    {
                        id: 'osm',
                        type: 'raster',
                        source: 'osm'
                    }
                ]
            },
            center: [this.lng, this.lat],
            zoom: this.zoom
        });

        this.map.addControl(new maplibregl.NavigationControl(), 'top-right');

        // Add single marker if provided
        if (lat && lng && !markers) {
            let marker = new maplibregl.Marker({ color: '#14b8a6' })
                .setLngLat([this.lng, this.lat])
                .addTo(this.map);

            if (this.markerTitle) {
                marker.setPopup(
                    new maplibregl.Popup({ offset: 25 })
                        .setHTML(`<div class="text-slate-900 font-semibold px-2 py-1">${this.markerTitle}</div>`)
                );
            }
        }

        // Add multiple markers
        if (this.markersData && this.markersData.length > 0) {
            const bounds = new maplibregl.LngLatBounds();
            this.markersData.forEach(m => {
                if (m.lat && m.lng) {
                    let marker = new maplibregl.Marker({ color: '#14b8a6' })
                        .setLngLat([m.lng, m.lat])
                        .addTo(this.map);
                    
                    if (m.title) {
                        marker.setPopup(
                            new maplibregl.Popup({ offset: 25 })
                                .setHTML(`<div class="text-slate-900 font-semibold px-2 py-1">${m.title}</div>`)
                        );
                    }
                    bounds.extend([m.lng, m.lat]);
                }
            });

            if (this.markersData.length > 1) {
                this.map.fitBounds(bounds, { padding: 50, maxZoom: 14 });
            } else if (this.markersData.length === 1) {
                this.map.setCenter([this.markersData[0].lng, this.markersData[0].lat]);
                this.map.setZoom(12);
            }
        }
    }
});
