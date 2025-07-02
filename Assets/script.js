// No início do script.js
console.log("script.js carregado.");

// Adicionar classe 'loaded' quando a página estiver totalmente carregada
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
    console.log("Página totalmente carregada, classe 'loaded' adicionada");
});

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOMContentLoaded disparado.");

    const bgMusic = document.getElementById('bgMusic');
    if (bgMusic) { // Adicionei verificação para garantir que o elemento existe
        bgMusic.loop = true;
        bgMusic.play().catch(error => {
            console.log("Erro ao tentar tocar música automaticamente:", error);
        });
    } else {
        console.warn("Elemento #bgMusic não encontrado.");
    }
});

function openImage(imagePath) {
    const imageViewer = document.getElementById('imageViewer');
    const viewerImage = document.getElementById('viewerImage');
    
    viewerImage.src = imagePath;
    imageViewer.classList.add('show');
}

document.addEventListener('DOMContentLoaded', function() {
    const imageViewer = document.getElementById('imageViewer');
    
    // Adicionar evento de clique em todas as imagens
    document.querySelectorAll('.card-image').forEach(img => {
        img.onclick = function() {
            openImage(this.src);
        }
    });

    // Fechar ao clicar no X
    document.querySelector('.close').onclick = function() {
        imageViewer.classList.remove('show');
    }

    // Fechar ao clicar fora da imagem
    imageViewer.onclick = function(event) {
        if (event.target === imageViewer) {
            imageViewer.classList.remove('show');
        }
    }

    // Fechar com ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            imageViewer.classList.remove('show');
        }
    });
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

// Controle do Formulário (Correção dos IDs) - Verifique se 'loginForm' existe em todas as páginas que usam este script
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) { // Verifica se o formulário existe na página
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            console.log('Dados do Formulário:', Object.fromEntries(formData));
            this.submit();
        });
    }
});


function toggleTheme() {
    const html = document.documentElement;
    const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    const logo = document.getElementById('themeLogo'); // Certifique-se de que este elemento existe na página
    
    // Alternar tema
    html.setAttribute('data-theme', newTheme);
    
    // Alternar logo (apenas se o logo existir)
    if (logo) {
        logo.src = newTheme === 'dark' 
            ? '../Assets/img/logofoto2.png' 
            : '../Assets/img/logofoto1.png';
    }
    
    localStorage.setItem('theme', newTheme);
}

// Carregar tema salvo
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    const logo = document.getElementById('themeLogo'); // Pegar o elemento logo aqui também

    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
        if (logo) { // Aplicar o logo apenas se ele existir
            logo.src = savedTheme === 'dark' 
                ? '../Assets/img/logofoto2.png' 
                : '../Assets/img/logofoto1.png';
        }
    }
});