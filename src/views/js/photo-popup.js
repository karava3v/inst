

let instagramImages = document.querySelectorAll('.gallery-image');


Array.from(instagramImages).forEach(photo => {
    photo.addEventListener('click', openPhotoDetails);
});

function openPhotoDetails( event ) {
    let photo = event.target,
        caption = photo.getAttribute('alt'),
        date = new Date(photo.dataset.taken);


}

function handleKeyDown (event) {
    const keyCode = event.keyCode || event.which;
    if (keyCode === 27) {
        event.preventDefault();
    }
}
document.addEventListener('keydown', handleKeyDown);