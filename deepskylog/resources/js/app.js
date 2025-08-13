import "./bootstrap";
import "./share-buttons.js";

import "./../../vendor/power-components/livewire-powergrid/dist/powergrid";
import "./../../vendor/power-components/livewire-powergrid/dist/tailwind.css";

//@formatter:off
import flatpickr from "flatpickr";
flatpickr.l10ns.default.firstDayOfWeek = 1;
//@formatter:on

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
window.L = L;
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';
// Fix Leaflet marker icon paths for Vite
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
	iconRetinaUrl: markerIcon2x,
	iconUrl: markerIcon,
	shadowUrl: markerShadow,
});