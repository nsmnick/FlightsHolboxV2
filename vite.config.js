import { readFileSync } from "fs";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import { ViteImageOptimizer } from "vite-plugin-image-optimizer";
import eslint from "vite-plugin-eslint";

// The page loads over https://flightsholboxv1.local (Local's trusted cert), so
// the dev server also needs to serve https:// — browsers block/auto-upgrade
// (and then fail) plain-http subresources like stylesheets on an https page,
// which otherwise silently breaks CSS with no visible error in a fresh
// browser profile that hasn't already granted an insecure-content exception.
const localCertDir =
  "/Users/jacob/Library/Application Support/Local/run/router/nginx/certs";

export default defineConfig({
  base: "/wp-content/themes/flightsholbox/dist",
  server: {
    port: 5274,
    host: "0.0.0.0",
    origin: "https://localhost:5274",
    cors: true,
    https: {
      key: readFileSync(`${localCertDir}/flightsholboxv1.local.key`),
      cert: readFileSync(`${localCertDir}/flightsholboxv1.local.crt`),
    },
  },
  build: {
    manifest: true,
    rollupOptions: {
      input: {
        index: "./assets/index.js",
        login: "./assets/login.js",
      },
    },
    outDir: "./public/wp-content/themes/flightsholbox/dist",
    copyPublicDir: false,
    assetsDir: "assets",
    cssCodeSplit: true,
    sourcemap: true,
  },
  plugins: [ViteImageOptimizer({}), vue(), eslint()],
});
