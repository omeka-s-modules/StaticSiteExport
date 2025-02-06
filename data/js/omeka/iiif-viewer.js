document.addEventListener('DOMContentLoaded', function(event) {
    document.querySelectorAll('.omeka-iiif-viewer').forEach(function(iiifViewer) {
        Mirador.viewer({
            id: iiifViewer.id,
            workspaceControlPanel: {
                enabled: false
            },
            workspace: {
                showZoomControls: true
            },
            window: {
                allowClose: false,
                allowFullscreen: true,
                allowMaximize: false,
                sideBarOpen: true,
                defaultSidebarPanelWidth: 300
            },
            osdConfig: {
                maxZoomPixelRatio: 100
            },
            windows: [
                {
                    manifestId: iiifViewer.dataset.manifestId,
                    thumbnailNavigationPosition: "far-bottom"
                }
            ]
        });
    });
});
