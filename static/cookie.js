function uniqid (prefix, moreEntropy) {
  //  discuss at: http://locutus.io/php/uniqid/
  // original by: Kevin van Zonneveld (http://kvz.io)
  //  revised by: Kankrelune (http://www.webfaktory.info/)
  //      note 1: Uses an internal counter (in locutus global) to avoid collision
  //   example 1: var $id = uniqid()
  //   example 1: var $result = $id.length === 13
  //   returns 1: true
  //   example 2: var $id = uniqid('foo')
  //   example 2: var $result = $id.length === (13 + 'foo'.length)
  //   returns 2: true
  //   example 3: var $id = uniqid('bar', true)
  //   example 3: var $result = $id.length === (23 + 'bar'.length)
  //   returns 3: true

  if (typeof prefix === 'undefined') {
    prefix = ''
  }

  var retId
  var _formatSeed = function (seed, reqWidth) {
    seed = parseInt(seed, 10).toString(16) // to hex str
    if (reqWidth < seed.length) {
      // so long we split
      return seed.slice(seed.length - reqWidth)
    }
    if (reqWidth > seed.length) {
      // so short we pad
      return Array(1 + (reqWidth - seed.length)).join('0') + seed
    }
    return seed
  }

  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}

  if (!$locutus.php.uniqidSeed) {
    // init seed with big random int
    $locutus.php.uniqidSeed = Math.floor(Math.random() * 0x75bcd15)
  }
  $locutus.php.uniqidSeed++

  // start with prefix, add current milliseconds hex string
  retId = prefix
  retId += _formatSeed(parseInt(new Date().getTime() / 1000, 10), 8)
  // add seed hex string
  retId += _formatSeed($locutus.php.uniqidSeed, 5)
  if (moreEntropy) {
    // for more entropy we add a float lower to 10
    retId += (Math.random() * 10).toFixed(10).toString()
  }

  return retId
}

function createID() {
	var id = uniqid('sh-uuid-', true);
    var today = new Date(), expires = new Date();
    expires.setTime(today.getTime() + (2 * 365 * 24 * 60 * 60 * 1000));
    document.cookie = "id=" + encodeURIComponent(id) + ";expires=" + expires.toGMTString();
}

function getID() {
    var oRegex = new RegExp("(?:; )?" + "id" + "=([^;]*);?");

    if (oRegex.test(document.cookie)) {
        return decodeURIComponent(RegExp["$1"]);
    } else {
        createID();
        return getID();
    }
}
function bookmark() {
    document.getElementById("bookmark").setAttribute('href', "javascript:(function () {var d = document;var w = window;var enc = encodeURIComponent;var f = '"+window.location.href+"index.php';var l = d.location;var p = '?shorten=' + enc(l.href) + '&comment=' + enc(d.title) + '&userID="+getID()+"';var u = f + p;var a = function () {if (!w.open(u))l.href = u;};if (/Firefox/.test(navigator.userAgent))setTimeout(a, 0); else a();void(0);})();");
}

document.getElementById("see_shorts").setAttribute('href', "list.php?userID=" + getID());
document.getElementById("userID2").value = getID();

bookmark();
