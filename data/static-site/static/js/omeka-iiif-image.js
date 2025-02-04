document.addEventListener('DOMContentLoaded', function(event) {
    document.querySelectorAll('.omeka-iiif-image').forEach(function(iiifImage) {
        OpenSeadragon({
            element: iiifImage,
            prefixUrl: iiifImage.dataset.prefixUrl,
            tileSources: [JSON.parse(iiifImage.dataset.tileSources)]
        });
    });
});
