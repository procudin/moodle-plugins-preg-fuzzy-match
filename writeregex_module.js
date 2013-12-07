/**
 * Created by M. Navrotskiy on 05.12.13.
 */
M.writeregex_module = (function() {
    var self = {

        wre_cre : null,

        init : function() {

            this.wre_cre = document.getElementById('wre_cre_percentage');

            this.wre_cre.onChange(function() {
               alert('qwe');
            });
        },

        wre_cre_edit : function(value) {
            alert('value');
        }
    };

    return self;
})();
