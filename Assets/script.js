// Controle de Música
document.addEventListener('DOMContentLoaded', function() {
    const bgMusic = document.getElementById('bgMusic');
    bgMusic.loop = true;
    
    // Tenta iniciar automaticamente
    const playPromise = bgMusic.play();
    
    // Se falhar, inicia no primeiro clique
    if (playPromise !== undefined) {
        playPromise.catch(error => {
            const startMusic = () => {
                bgMusic.play();
                document.body.removeEventListener('click', startMusic);
            };
            document.body.addEventListener('click', startMusic);
        });
    }
});

// Efeitos dos Botões
document.querySelectorAll('button').forEach(button => {
    button.addEventListener('mousedown', () => {
        button.style.transform = 'translateY(4px)';
    });
    
    button.addEventListener('mouseup', () => {
        button.style.transform = 'translateY(0)';
    });
});

// Controle do Formulário (Correção dos IDs)
document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    console.log('Dados do Formulário:', Object.fromEntries(formData));
    this.submit();
});


function toggleTheme() {
    const html = document.documentElement;
    const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    const logo = document.getElementById('themeLogo');
    
    // Alternar tema
    html.setAttribute('data-theme', newTheme);
    
    // Alternar logo
    logo.src = newTheme === 'dark' 
        ? '../Assets/img/logofoto2.png' 
        : '../Assets/img/logofoto1.png';
    
    localStorage.setItem('theme', newTheme);
}

// Carregar tema salvo
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
    document.documentElement.setAttribute('data-theme', savedTheme);
    document.getElementById('themeLogo').src = savedTheme === 'dark' 
    ? '../Assets/img/logofoto2.png' 
    : '../Assets/img/logofoto1.png';
}