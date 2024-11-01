/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

eval("// The two lines below run React without JSX - no tooling needed\n// Note: They run from main.js initially, and are overwritten when the tooling is activated\n\nvar pEl = wp.element.createElement(\"p\", {}, \"Hello WP from React.\");\nwp.element.render(pEl, document.querySelector('.welcome-panel'));\n\n// With tooling set up, uncomment the following two lines to run React with simple JSX\n\n// const Hello = () => <p>Hello WP! React here, with JSX.</p>;\n// wp.element.render(<Hello />, document.querySelector( '.entry-content' ) );\n\n\n//With tooling set up, uncomment the following two lines to check WP with interactive react\n\n// import Counter from './Counter';\n// wp.element.render(<Counter />, document.querySelector( '.entry-content' ) );//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL3NyYy9pbmRleC5qcz84NzQ5Il0sInNvdXJjZXNDb250ZW50IjpbIi8vIFRoZSB0d28gbGluZXMgYmVsb3cgcnVuIFJlYWN0IHdpdGhvdXQgSlNYIC0gbm8gdG9vbGluZyBuZWVkZWRcbi8vIE5vdGU6IFRoZXkgcnVuIGZyb20gbWFpbi5qcyBpbml0aWFsbHksIGFuZCBhcmUgb3ZlcndyaXR0ZW4gd2hlbiB0aGUgdG9vbGluZyBpcyBhY3RpdmF0ZWRcblxudmFyIHBFbCA9IHdwLmVsZW1lbnQuY3JlYXRlRWxlbWVudChcInBcIiwge30sIFwiSGVsbG8gV1AgZnJvbSBSZWFjdC5cIik7XG53cC5lbGVtZW50LnJlbmRlcihwRWwsIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53ZWxjb21lLXBhbmVsJykpO1xuXG4vLyBXaXRoIHRvb2xpbmcgc2V0IHVwLCB1bmNvbW1lbnQgdGhlIGZvbGxvd2luZyB0d28gbGluZXMgdG8gcnVuIFJlYWN0IHdpdGggc2ltcGxlIEpTWFxuXG4vLyBjb25zdCBIZWxsbyA9ICgpID0+IDxwPkhlbGxvIFdQISBSZWFjdCBoZXJlLCB3aXRoIEpTWC48L3A+O1xuLy8gd3AuZWxlbWVudC5yZW5kZXIoPEhlbGxvIC8+LCBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLmVudHJ5LWNvbnRlbnQnICkgKTtcblxuXG4vL1dpdGggdG9vbGluZyBzZXQgdXAsIHVuY29tbWVudCB0aGUgZm9sbG93aW5nIHR3byBsaW5lcyB0byBjaGVjayBXUCB3aXRoIGludGVyYWN0aXZlIHJlYWN0XG5cbi8vIGltcG9ydCBDb3VudGVyIGZyb20gJy4vQ291bnRlcic7XG4vLyB3cC5lbGVtZW50LnJlbmRlcig8Q291bnRlciAvPiwgZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy5lbnRyeS1jb250ZW50JyApICk7XG5cblxuLy8vLy8vLy8vLy8vLy8vLy8vXG4vLyBXRUJQQUNLIEZPT1RFUlxuLy8gLi9zcmMvaW5kZXguanNcbi8vIG1vZHVsZSBpZCA9IDBcbi8vIG1vZHVsZSBjaHVua3MgPSAwIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///0\n");

/***/ })
/******/ ]);