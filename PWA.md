# 📱 PWA - Progressive Web App
## Portal do Funcionário

## ✅ Funcionalidades Implementadas

O Portal do Funcionário agora é um **Progressive Web App (PWA)**, o que permite:

- ✅ Instalar como app nativo no smartphone
- ✅ Funcionar offline (parcialmente)
- ✅ Ícone personalizado na tela inicial
- ✅ Experiência full-screen (sem barra do navegador)
- ✅ Carregamento rápido com cache
- ✅ Atualizações automáticas em background

---

## 📲 Como Instalar

### Android (Chrome)

1. Abra o site no Chrome: `http://localhost/portal-funcionario`
2. Aguarde o banner "Adicionar Portal Funcionário à tela inicial"
3. OU clique no menu (⋮) → "Adicionar à tela inicial"
4. Confirme em "Adicionar"
5. O ícone aparecerá na sua tela inicial

### iOS (Safari)

1. Abra o site no Safari: `http://localhost/portal-funcionario`
2. Toque no botão **Compartilhar** (quadrado com seta)
3. Role para baixo e toque em **"Adicionar à Tela de Início"**
4. Edite o nome se desejar (opcional)
5. Toque em **"Adicionar"** no canto superior direito
6. O ícone aparecerá na sua tela inicial

---

## 🎨 Personalização dos Ícones

### Ícones Atuais (Placeholder)
Atualmente existem ícones temporários. Para personalizar:

1. **Crie os ícones** em PNG:
   - `icon-192.png` (192x192px) - Android home screen
   - `icon-512.png` (512x512px) - Play Store
   - `apple-touch-icon.png` (180x180px) - iOS home screen

2. **Salve em**: `public/icons/`

3. **Dicas de design**:
   - Use o logo da empresa
   - Fundo branco ou transparente (iOS requer fundo sólido)
   - Margem de 16% ao redor do logo
   - Cores: `#3f9cae` (teal) e branco

### Ferramentas para Criar Ícones
- **RealFaviconGenerator**: https://realfavicongenerator.net/
- **Canva**: https://canva.com
- **Figma**: https://figma.com

---

## 🔧 Arquivos Criados

```
public/
├── manifest.json           # Configuração do PWA
├── sw.js                   # Service Worker (cache/offline)
└── icons/
    ├── README.md           # Instruções para ícones
    ├── icon.svg            # Ícone SVG (placeholder)
    ├── icon-192.png        # (a criar)
    ├── icon-512.png        # (a criar)
    └── apple-touch-icon.png # (a criar)

resources/views/components/
└── portal-funcionario-layout.blade.php (modificado)
    ├── Meta tags PWA
    ├── Links para manifest e ícones
    └── Script de registro do Service Worker
```

---

## 📁 Estrutura do Manifest

```json
{
  "name": "Gestor Alfa - Portal do Funcionário",
  "short_name": "Portal Funcionário",
  "start_url": "/portal-funcionario",
  "display": "standalone",
  "theme_color": "#3f9cae",
  "background_color": "#ffffff"
}
```

### Explicação dos Campos

| Campo | Descrição |
|-------|-----------|
| `name` | Nome completo do app |
| `short_name` | Nome na tela inicial (limite de caracteres) |
| `start_url` | Página inicial ao abrir o app |
| `display` | `standalone` = sem UI do navegador |
| `theme_color` | Cor da barra de status (teal) |
| `background_color` | Cor de fundo (branco) |

---

## 🌐 Service Worker

O Service Worker (`sw.js`) gerencia:

### Cache
- Cache de assets estáticos (CSS, JS)
- Atualização em background
- Limpeza de caches antigos

### Offline
- Funcionalidade básica offline
- Fallback para páginas em cache
- Mensagem de offline (futuro)

### Performance
- Respostas mais rápidas do cache
- Network-first para conteúdo dinâmico
- Cache-first para assets estáticos

---

## 🧪 Testando o PWA

### Chrome DevTools

1. **Abra o site** no Chrome
2. **F12** → DevTools
3. **Application tab**:
   - **Manifest**: Verifique se carrega sem erros
   - **Service Workers**: Deve estar "activated"
   - **Cache Storage**: Veja os assets em cache

4. **Lighthouse tab**:
   - Run audit
   - Categoria "PWA"
   - Score deve ser 100

### Android (Dispositivo Real)

1. Acesse `http://localhost/portal-funcionario`
2. Menu → "Adicionar à tela inicial"
3. Ícone deve aparecer na home
4. Abra o app → deve abrir full-screen
5. Verifique offline (avião) → cache funciona

### iOS (Dispositivo Real)

1. Acesse no Safari
2. Share → "Add to Home Screen"
3. Ícone aparece na home
4. Abra → full-screen com status bar teal
5. Verifique offline

---

## ⚠️ Considerações Importantes

### HTTPS Obrigatório
- **Produção**: HTTPS é obrigatório para PWA
- **Desenvolvimento**: HTTP localhost funciona
- **Staging**: Requer HTTPS

### Limitações iOS
- iOS não mostra banner automático
- Usuário deve adicionar manualmente
- Service Worker tem limitações
- Ícone requer fundo sólido (sem transparência)

### Limitações Android
- Banner automático após critérios de uso
- Ícones adaptativos (maskable) recomendados
- Melhor suporte geral a PWAs

### Cache
- Service Worker cacheia assets
- Conteúdo dinâmico requer estratégia específica
- Versão do cache no nome (`v1`, `v2`...)

---

## 🚀 Próximos Passos (Opcional)

### 1. Ícones Personalizados
- Criar ícones com logo da empresa
- Seguir guia em `public/icons/README.md`

### 2. Página Offline
- Criar `/offline` com mensagem amigável
- Adicionar fallback no Service Worker

### 3. Notificações Push
- Implementar notificações de novos chamados
- Requer backend e permissão do usuário

### 4. Sync em Background
- Sincronizar dados quando online
- `BackgroundSync` API

### 5. Atualizações
- Versionar cache (`v2`, `v3`...)
- Notificar usuário de atualizações

---

## 📞 Suporte

### Problemas Comuns

**Ícone não aparece no iOS**
- Verifique se `apple-touch-icon.png` existe
- iOS requer PNG com fundo sólido
- Limpe cache do Safari

**Banner não aparece no Android**
- Usuário deve interagir com o site
- Service Worker deve estar ativo
- HTTPS em produção

**App não abre offline**
- Verifique Service Worker no DevTools
- Assets devem estar em cache
- Implementar fallback offline

---

## 📚 Recursos Úteis

- [MDN PWA Guide](https://developer.mozilla.org/pt-BR/docs/Web/Progressive_web_apps)
- [Google PWA Checklist](https://web.dev/pwa-checklist/)
- [Web App Manifest](https://developer.mozilla.org/pt-BR/docs/Web/Manifest)
- [Service Workers](https://developer.mozilla.org/pt-BR/docs/Web/API/Service_Worker_API)

---

**Implementado em:** Março 2026
**Versão:** 1.0.0
