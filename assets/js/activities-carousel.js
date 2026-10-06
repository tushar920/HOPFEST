(function(){
  function init(){
    const section=document.querySelector('#what');
    const track=section&&section.querySelector('.mpg-grid');
    if(!section||!track)return;
    const slides=[
      {name:'OBSTACLE RACES',img:'assets/images/Hopfest4.png',tone:'#e84b81'},
      {name:'FAMILY FITNESS CHALLENGES',img:'assets/images/Hopfest1.png',tone:'#faee30'},
      {name:'WALL CLIMBING',img:'assets/images/Hopfest5.png',tone:'#27aeef'},
      {name:'BALANCE TEST',img:'assets/images/Hopfest4.png',tone:'#e84b81'},
      {name:'TREASURE HUNT',img:'assets/images/Hopfest3.png',tone:'#faee30'},
      {name:'ENTERTAINMENT HUB',img:'assets/images/Hopfest_fisheye2.png',tone:'#27aeef'}
    ];
    track.innerHTML='';
    track.classList.add('hf-activity-track');
    slides.forEach((s,i)=>{
      const slide=document.createElement('article');
      slide.className='hf-activity-slide'+(i===0?' is-active':'');
      slide.dataset.index=i;
      slide.innerHTML='<img src="'+s.img+'" alt="'+s.name+' at HOP FEST"><div class="hf-activity-wash"></div><div class="hf-activity-title" style="--activity-tone:'+s.tone+'">'+s.name.replace(' ','<br>')+'</div>';
      track.appendChild(slide);
    });
    const controls=document.createElement('div');
    controls.className='hf-carousel-controls';
    controls.innerHTML='<button class="hf-prev" aria-label="Previous activity">←</button><div class="hf-dots" role="tablist" aria-label="Activities"></div><button class="hf-next" aria-label="Next activity">→</button>';
    section.appendChild(controls);
    const dots=controls.querySelector('.hf-dots');
    slides.forEach((s,i)=>{const b=document.createElement('button');b.className='hf-dot'+(i===0?' is-active':'');b.type='button';b.setAttribute('aria-label',s.name);b.addEventListener('click',()=>go(i));dots.appendChild(b);});
    let current=0;
    function go(index){current=(index+slides.length)%slides.length;track.scrollTo({left:current*track.clientWidth,behavior:'smooth'});track.querySelectorAll('.hf-activity-slide').forEach((el,i)=>el.classList.toggle('is-active',i===current));dots.querySelectorAll('.hf-dot').forEach((el,i)=>el.classList.toggle('is-active',i===current));}
    controls.querySelector('.hf-prev').addEventListener('click',()=>go(current-1));
    controls.querySelector('.hf-next').addEventListener('click',()=>go(current+1));
    let startX=0;
    track.addEventListener('touchstart',e=>{startX=e.touches[0].clientX},{passive:true});
    track.addEventListener('touchend',e=>{const dx=e.changedTouches[0].clientX-startX;if(Math.abs(dx)>45)go(current+(dx<0?1:-1));},{passive:true});
    track.addEventListener('scroll',()=>{const idx=Math.round(track.scrollLeft/track.clientWidth);if(idx!==current&&idx>=0&&idx<slides.length){current=idx;track.querySelectorAll('.hf-activity-slide').forEach((el,i)=>el.classList.toggle('is-active',i===current));dots.querySelectorAll('.hf-dot').forEach((el,i)=>el.classList.toggle('is-active',i===current));}});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
