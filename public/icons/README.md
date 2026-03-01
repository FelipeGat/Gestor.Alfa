# 📱 Ícones PWA - Portal do Funcionário

## Ícones Necessários

Para que o PWA funcione corretamente em todos os dispositivos, você precisa dos seguintes ícones em formato **PNG**:

### 1. **icon-192.png** (192x192 pixels)
- Usado na tela inicial do Android
- Local: `public/icons/icon-192.png`

### 2. **icon-512.png** (512x512 pixels)
- Usado na Play Store e splash screen
- Local: `public/icons/icon-512.png`

### 3. **apple-touch-icon.png** (180x180 pixels)
- Usado na tela inicial do iOS
- Local: `public/icons/apple-touch-icon.png`

## Como Gerar os Ícones

### Opção 1: Usando o Figma/Photoshop
1. Crie um artefato quadrado (512x512)
2. Adicione o logo da empresa centralizado
3. Use fundo transparente ou branco
4. Exporte nas 3 tamanhos mencionados

### Opção 2: Usando Ferramentas Online
- **RealFaviconGenerator**: https://realfavicongenerator.net/
- **PWA Icons Generator**: https://pwainit.com/generate-icons
- **Figma to Icons**: https://www.figma.com/community/plugin/...

### Opção 3: Usando o Ícone SVG Incluído
O arquivo `icon.svg` foi criado como placeholder. Você pode:
1. Abrir em um editor SVG
2. Modificar cores e design
3. Exportar como PNG nos tamanhos necessários

## Especificações de Design

### Cores Sugeridas
- **Primária**: `#3f9cae` (Teal - cor do sistema)
- **Fundo**: `#ffffff` (Branco) ou transparente
- **Contraste**: Garantir bom contraste para visibilidade

### Formato
- **Android**: PNG com fundo transparente ou sólido
- **iOS**: PNG com fundo sólido (iOS não suporta transparência)
- **Maskable**: Usar `purpose: "maskable"` para ícones adaptativos

### Margem de Segurança
- Deixar pelo menos **16%** de margem em torno do logo
- Importante para ícones "maskable" do Android

## Exemplo de Ícone Ideal

```
┌─────────────────────────┐
│                         │
│    ┌─────────────┐      │
│    │             │      │
│    │   LOGO      │      │  ← Logo centralizado
│    │             │      │     com 68% do tamanho
│    └─────────────┘      │
│                         │
│  (16% margin around)    │
└─────────────────────────┘
```

## Após Gerar os Ícones

1. Coloque os arquivos PNG na pasta `public/icons/`
2. Nomeie corretamente:
   - `icon-192.png`
   - `icon-512.png`
   - `apple-touch-icon.png`
3. Limpe o cache: `php artisan view:clear`
4. Teste em dispositivos reais

## Testando o PWA

### Chrome DevTools
1. Abra o site no Chrome
2. F12 → Application → Manifest
3. Verifique se os ícones carregam
4. Teste em "Add to home screen"

### Android
1. Abra no Chrome
2. Menu → "Adicionar à tela inicial"
3. Verifique se o ícone aparece corretamente

### iOS
1. Abra no Safari
2. Share → "Add to Home Screen"
3. Verifique o ícone na tela de confirmação

## Notas Importantes

⚠️ **iOS não suporta ícones com transparência** - use fundo sólido
⚠️ **Android Adaptive Icons** requerem margem de segurança
⚠️ **Sempre teste em dispositivos reais** antes de publicar
