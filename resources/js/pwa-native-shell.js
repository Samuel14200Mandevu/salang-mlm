/**
 * Block iOS Safari pinch-to-zoom (gesturestart) for installed / mobile PWA feel.
 * Does not touch frozen auth/splash scripts beyond shared Vite bundle load.
 */
(function blockIosPinchZoom() {
  const prevent = (event) => {
    event.preventDefault();
  };

  document.addEventListener('gesturestart', prevent, { passive: false });
  document.addEventListener('gesturechange', prevent, { passive: false });
  document.addEventListener('gestureend', prevent, { passive: false });
})();
