(function(){

    var figures = document.querySelectorAll('figure');
    var subscribeForm = document.getElementById( 'subscribe-float' );

    inView( 'figure' ).on( 'enter', function( figure ) {

        var img = figure.querySelector( 'img' );

        if (  'undefined' !== typeof img.dataset.src ) {

            figure.classList.add( 'is-loading' );

            newImg = new Image();
            newImg.src = img.dataset.src;
            newImg.addEventListener( 'load', function() {

                figure.innerHTML = '';
                figure.appendChild( this );

                setTimeout( function() {
                    figure.classList.remove( 'is-loading' );
                    figure.classList.add( 'is-loaded' );
                }, 300 );
            } );
        }
    } );

    inView( '.content-item' )
        .on( 'enter', function( item ) {
            item.classList.add( 'highlight' );
        } )
        .on( 'exit', function( item ) {
            item.classList.remove( 'highlight' );
        } );

    inView( '.content-item:last-child' )
        .on( 'enter', function( item ) {
            subscribeForm.classList.add( 'slide-in' );
        } )
        .on( 'exit', function( item ) {
            subscribeForm.classList.remove( 'slide-in' );
        } );
})();