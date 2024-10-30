(function($) {

  'use strict';

  $(document).ready(function() {

    'use strict';

    //Options Menu -------------------------------------------------------------
    let select2_elements = [];
    select2_elements.push('#daextctwga-conversion-id');
    select2_elements.push('#daextctwga-global-site-tag');
    select2_elements.push('#daextctwga-require-cookie');

    jQuery(select2_elements.join(',')).select2();

  });

})(window.jQuery);