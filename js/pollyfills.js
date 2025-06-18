// Polyfill for older browsers
;(() => {
  // Polyfill for Element.matches
    if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector
    }

  // Polyfill for Element.closest
    if (!Element.prototype.closest) {
    Element.prototype.closest = function (s) {
        var el = this
        do {
        if (el.matches(s)) return el
        el = el.parentElement || el.parentNode
        } while (el !== null && el.nodeType === 1)
            return null
    }
    }

  // Polyfill for forEach on NodeList
    if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach
    }

  // Polyfill for Object.assign
    if (typeof Object.assign !== "function") {
    Object.assign = (target) => {
        if (target === null || target === undefined) {
        throw new TypeError("Cannot convert undefined or null to object")
        }

        var to = Object(target)

        for (var index = 1; index < arguments.length; index++) {
        var nextSource = arguments[index]

        if (nextSource !== null && nextSource !== undefined) {
            for (var nextKey in nextSource) {
            if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                to[nextKey] = nextSource[nextKey]
                }
            }
        }
        } 
        return to
    }
    }

  // Polyfill for fetch
    if (!window.fetch) {
    console.log("Fetch API not found, loading polyfill...")
    var fetchScript = document.createElement("script")
    fetchScript.src = "https://cdn.jsdelivr.net/npm/whatwg-fetch@3.6.2/dist/fetch.umd.min.js"
    document.head.appendChild(fetchScript)
    }

  // Polyfill for Promise
    if (!window.Promise) {
    console.log("Promise not found, loading polyfill...")
    var promiseScript = document.createElement("script")
    promiseScript.src = "https://cdn.jsdelivr.net/npm/promise-polyfill@8.2.3/dist/polyfill.min.js"
    document.head.appendChild(promiseScript)
    }
})()
