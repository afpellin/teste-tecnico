document.addEventListener('DOMContentLoaded', () => {
    carregarPostagens();

    // Formulário de nova postagem
    document.getElementById('form-postagem').addEventListener('submit', criarPostagem);
});

async function carregarPostagens() {
    try {
        const response = await fetch('index.php?rota=postagens');
        const postagens = await response.json();

        const container = document.getElementById('postagens-container');
        container.innerHTML = '';

        postagens.forEach(post => {
            const elemento = criarElementoPostagem(post);
            container.appendChild(elemento);
        });
    } catch (error) {
        console.error('Erro ao carregar postagens:', error);
    }
}

function criarElementoPostagem(post) {
    const div = document.createElement('div');
    div.className = 'postagem';
    div.innerHTML = `
        <h3>${post.titulo}</h3>
        <p>${post.conteudo}</p>
        <small>Por ${post.autor} em ${new Date(post.created_at).toLocaleString()}</small>
        
        <div class="acoes">
            <a href="ver_postagem.html?id=${post.id}" class="btn-ver">Ver</a>
            ${post.usuario_id === usuarioLogado.id ? `
                <button class="btn-editar" data-id="${post.id}">Editar</button>
                <button class="btn-excluir" data-id="${post.id}">Excluir</button>
            ` : ''}
        </div>
    `;

    return div;
}

async function criarPostagem(e) {
    e.preventDefault();

    const formData = new FormData(e.target);

    try {
        const response = await fetch('index.php?rota=criar_postagem', {
            method: 'POST',
            body: formData
        });

        const resultado = await response.json();

        if (resultado.success) {
            e.target.reset();
            carregarPostagens();
        } else {
            alert(resultado.error || 'Erro ao criar postagem');
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}