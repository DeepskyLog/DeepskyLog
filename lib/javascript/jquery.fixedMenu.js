/* @version 1.1 fixedMenu
 * @author Lucas Forchino
 * @webSite: http://www.jqueryload.com
 * jquery top fixed menu
 */
(function($){
    $.fn.fixedMenu=function(){
        return this.each(function(){
            var menu= $(this);
            menu.find('ul li > a').bind('click',function(){
            if ($(this).parent().hasClass('active')){
                $(this).parent().removeClass('active');
            }
            else{
                $(this).parent().parent().find('.active').removeClass('active');
                $(this).parent().addClass('active');
            }
            })
        });
    }
})(jQuery);
