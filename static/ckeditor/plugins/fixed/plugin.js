/*
*   Plugin developed by Netbroad, C.B.
*
*   LICENCE: GPL, LGPL, MPL
*   NON-COMMERCIAL PLUGIN.
*
*   Website: netbroad.eu
*   Twitter: @netbroadcb
*   Facebook: Netbroad
*   LinkedIn: Netbroad
*
*/

CKEDITOR.plugins.add( 'fixed', {
	
    init: function( editor ) {
        window.addEventListener('scroll', function(){
            var content             = document.getElementsByClassName('cke_contents').item(0);
            var toolbar             = document.getElementsByClassName('cke_top').item(0);
            var editor              = document.getElementsByClassName('cke').item(0);
            var inner               = document.getElementsByClassName('cke_inner').item(0);
            var scrollvalue         = document.documentElement.scrollTop > document.body.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
            var Y = editor.getBoundingClientRect().top;
            if(Y<=0){
	            toolbar.style.position   = "fixed";
	            toolbar.style.width     = content.offsetWidth + "px";
	            toolbar.style.top       = "0px";
	            toolbar.style.margin    = "0 auto";
            	toolbar.style.boxSizing = "border-box";
            }else{
	            toolbar.style.position   = "relative";
            }
        }, false);
    }
});