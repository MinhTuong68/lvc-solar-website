(function(){
    const wrap = document.querySelector('.lvc-promo-wrap');
    if(!wrap) return;

    const track = wrap.querySelector('.lvc-promo-track');
    const items = wrap.querySelectorAll('.lvc-promo-item');
    const prev = wrap.querySelector('.lvc-promo-prev');
    const next = wrap.querySelector('.lvc-promo-next');

    let index = 0;

    function visibleCount(){
        return window.innerWidth <= 768 ? 1 : 2;
    }

    function updatePromo(){
        const gap = 14;
        const itemWidth = items[0].offsetWidth + gap;
        const maxIndex = Math.max(0, items.length - visibleCount());

        if(index > maxIndex) index = 0;
        if(index < 0) index = maxIndex;

        track.style.transform = `translateX(-${index * itemWidth}px)`;
    }

    next.addEventListener('click', function(){
        index++;
        updatePromo();
    });

    prev.addEventListener('click', function(){
        index--;
        updatePromo();
    });

    setInterval(function(){
        index++;
        updatePromo();
    }, 3500);

    window.addEventListener('resize', updatePromo);
    updatePromo();
})();