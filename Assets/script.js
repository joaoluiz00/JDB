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
    }    const imageViewer = document.getElementById('imageViewer');
    const viewerImage = document.getElementById('viewerImage');

    if (imageViewer) {
        console.log("imageViewer encontrado e inicializado");
        
        document.querySelectorAll('.card-image').forEach(img => {
            img.onclick = function() {
                console.log("Imagem clicada:", this.src);
                viewerImage.src = this.src;
                imageViewer.style.display = "flex";
                console.log("Viewer mostrado");
            }
        });

        document.querySelector('.close').onclick = function() {
            console.log("Botão fechar clicado");
            imageViewer.style.display = "none";
            console.log("Viewer ocultado");
        }

        imageViewer.onclick = function(event) {
            if (event.target === imageViewer) {
                console.log("Clicado fora da imagem no viewer");
                imageViewer.style.display = "none";
                console.log("Viewer ocultado por clique fora");
            }
        }
    } else {
        console.warn("Elemento #imageViewer não encontrado.");
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