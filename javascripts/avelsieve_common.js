var AVELSIEVE = AVELSIEVE || {};

AVELSIEVE.util = {
    showDiv: function(divname) {
        if($(divname)) {
            $(divname).style.display = "";
        }
        return false;
    },
    hideDiv: function(divname) {
        if($(divname)) {
            $(divname).style.display = "none";
        }
        return false;
    }
};

/**
 * @deprecated
 */
function el(id) {
  if (document.getElementById) {
    return document.getElementById(id);
  }
  return false;
}

