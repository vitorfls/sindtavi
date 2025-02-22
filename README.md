# SINDTAVI - Sistema de Gestão Sindical

Sistema desenvolvido para automatizar e gerenciar processos sindicais, incluindo cobrança de contribuições e comunicação com associados.

## 🚀 Funcionalidades

- 📧 Envio automatizado de e-mails em massa
- 💰 Integração com ASAAS para gestão de cobranças
- 📄 Geração e gestão de boletos
- 📁 Sistema de upload de documentos
- 💼 Gestão de associados

## 🛠️ Tecnologias

- PHP
- SendGrid para envio de e-mails
- ASAAS API para gestão financeira
- MySQL Database

## ⚙️ Configuração

1. Clone o repositório:
```bash
git clone https://github.com/vitorfls/sindtavi.git
```

2. Configure as variáveis de ambiente:
   - Copie o arquivo `.env.example` para `.env`
   - Configure as seguintes variáveis:
     - Banco de dados (DATABASE_*)
     - ASAAS API (ASAAS_*)
     - SMTP/SendGrid (SMTP_*)

3. Configure o banco de dados:
   - Host: DATABASE_HOST
   - Database: DATABASE_NAME
   - Usuário: DATABASE_USER
   - Senha: DATABASE_PASSWORD

## 🔧 Estrutura do Projeto

```
sindtavi/
├── src/
│   ├── BulkMailer.php    # Gerenciamento de envio de e-mails em massa
│   ├── Config.php        # Configurações do sistema
│   └── Sindicato.php     # Regras de negócio do sindicato
├── Templates/
│   └── enviar-email-cobranca.html    # Template de e-mail
├── uploads/
│   └── anexos/           # Diretório para upload de arquivos
└── boletos/              # Diretório para armazenamento de boletos
```

## 💳 Integração ASAAS

O sistema utiliza a API do ASAAS para:
- Geração de cobranças
- Split de pagamentos
- Gestão de carteira digital
- Geração de boletos

## 📧 Sistema de E-mail

- Utiliza SendGrid para envio de e-mails
- Templates HTML personalizados
- Suporte para anexos
- Rastreamento de envios

## 📁 Upload de Arquivos

Suporta upload de:
- PDF
- DOC
- DOCX

## 🔐 Segurança

- Todas as credenciais são gerenciadas via variáveis de ambiente
- Integração segura com APIs externas
- Validação de tipos de arquivos para upload
- Sanitização de dados de entrada

## 📝 Contribuição

1. Faça o fork do projeto
2. Crie sua feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add: nova funcionalidade'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença [MIT](LICENSE).
