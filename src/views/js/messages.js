document.querySelector('.messages__chats .chat').classList.add('chat--active');
document.querySelector('.messages__person:first-of-type').classList.add('messages__person--active');

document.addEventListener('DOMContentLoaded', () => {
    let containerHeight = document.querySelector('.messages__people').offsetHeight;
    document.querySelector('.messages__chats').style.maxHeight = containerHeight + 'px';
    document.querySelector('.message__chats-wrap').style.maxHeight = (containerHeight - 69) + 'px';
})



let friends = {
        list: document.querySelector('ul.messages__people'),
        all: document.querySelectorAll('.messages__people .messages__person'),
        avatar: '',
        name: '@nekaravaev'
    },
    chat = {
        container__msg: document.querySelector('.container__msg .messages__chats'),
        current: null,
        person: null,
        name: document.querySelector('.container__msg .messages__chats .messages__chat-top .messages__chat-name'),
        avatar: document.querySelector('.messages__chat-avatar')
    }

friends.all.forEach(f => {
    f.addEventListener('click', () => {
        f.classList.contains('messages__person--active') || setAciveChat(f)
    })
});

function setAciveChat(f) {
    friends.list.querySelector('.messages__person--active').classList.remove('messages__person--active')
    f.classList.add('messages__person--active')
    chat.current = chat.container__msg.querySelector('.chat--active')
    chat.messages__person = f.getAttribute('data-chat')
    chat.current.classList.remove('chat--active')
    chat.container__msg.querySelector('[data-chat="' + chat.messages__person + '"]').classList.add('chat--active')
    friends.name = f.querySelector('.messages__person-name').innerText
    friends.avatar = f.querySelector('.messages__person-avatar').getAttribute('src');
    chat.avatar.setAttribute('src', friends.avatar);
    chat.name.innerHTML = friends.name;
}