-- ============================================
-- MÓDULO: GESTÃO DE USUÁRIOS E AUTENTICAÇÃO
-- ============================================

CREATE TABLE instituicao (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nome VARCHAR(255) NOT NULL,
    sigla VARCHAR(20) NOT NULL,
    cnpj VARCHAR(18) UNIQUE,
    endereco JSONB,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuario (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    instituicao_id UUID REFERENCES instituicao(id),
    email VARCHAR(255) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    foto_perfil_url VARCHAR(500),
    tipo_usuario VARCHAR(20) NOT NULL CHECK (tipo_usuario IN ('ALUNO', 'ORIENTADOR', 'COORDENADOR', 'ADMIN')),
    status VARCHAR(20) DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO', 'BLOQUEADO', 'PENDENTE')),
    ultimo_acesso TIMESTAMP,
    email_verificado BOOLEAN DEFAULT false,
    token_verificacao VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT email_format CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$')
);

CREATE TABLE sessao_usuario (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID REFERENCES usuario(id) ON DELETE CASCADE,
    token_acesso VARCHAR(500) NOT NULL,
    token_refresh VARCHAR(500),
    ip_address INET,
    user_agent TEXT,
    expira_em TIMESTAMP NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- MÓDULO: ESTRUTURA ACADÊMICA
-- ============================================

CREATE TABLE departamento (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    instituicao_id UUID REFERENCES instituicao(id),
    nome VARCHAR(255) NOT NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE curso (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    departamento_id UUID REFERENCES departamento(id),
    nome VARCHAR(255) NOT NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nivel VARCHAR(50) NOT NULL CHECK (nivel IN ('GRADUACAO', 'ESPECIALIZACAO', 'MESTRADO', 'DOUTORADO')),
    duracao_semestres INTEGER,
    coordenador_id UUID REFERENCES usuario(id),
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE linha_pesquisa (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    curso_id UUID REFERENCES curso(id),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    area_conhecimento VARCHAR(100),
    palavras_chave TEXT[],
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE aluno (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID UNIQUE REFERENCES usuario(id) ON DELETE CASCADE,
    curso_id UUID REFERENCES curso(id),
    matricula VARCHAR(50) UNIQUE NOT NULL,
    data_ingresso DATE NOT NULL,
    data_prevista_conclusao DATE,
    lattes_url VARCHAR(500),
    orcid VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orientador (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID UNIQUE REFERENCES usuario(id) ON DELETE CASCADE,
    departamento_id UUID REFERENCES departamento(id),
    titulacao VARCHAR(50) NOT NULL CHECK (titulacao IN ('ESPECIALISTA', 'MESTRE', 'DOUTOR', 'POS_DOUTOR')),
    areas_atuacao TEXT[],
    lattes_url VARCHAR(500) NOT NULL,
    orcid VARCHAR(50),
    max_orientandos INTEGER DEFAULT 10,
    orientandos_atuais INTEGER DEFAULT 0,
    aceita_coorientacao BOOLEAN DEFAULT true,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- MÓDULO: TCC/TESE
-- ============================================

CREATE TABLE tcc (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    aluno_id UUID REFERENCES aluno(id) ON DELETE RESTRICT,
    curso_id UUID REFERENCES curso(id),
    linha_pesquisa_id UUID REFERENCES linha_pesquisa(id),
    
    titulo VARCHAR(500) NOT NULL,
    titulo_ingles VARCHAR(500),
    tipo_trabalho VARCHAR(50) NOT NULL CHECK (tipo_trabalho IN ('TCC', 'MONOGRAFIA', 'DISSERTACAO', 'TESE')),
    
    resumo TEXT,
    abstract TEXT,
    palavras_chave TEXT[],
    keywords TEXT[],
    
    status VARCHAR(50) DEFAULT 'RASCUNHO' CHECK (status IN (
        'RASCUNHO', 'EM_ORIENTACAO', 'AGUARDANDO_BANCA', 
        'BANCA_AGENDADA', 'EM_AVALIACAO', 'APROVADO', 
        'APROVADO_COM_RESSALVAS', 'REPROVADO', 'CANCELADO'
    )),
    
    data_inicio DATE,
    data_qualificacao DATE,
    data_defesa DATE,
    data_entrega_final DATE,
    
    nota_final DECIMAL(4,2),
    
    metadata JSONB, -- Campos customizáveis por instituição
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP -- Soft delete
);

CREATE TABLE orientacao (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tcc_id UUID REFERENCES tcc(id) ON DELETE CASCADE,
    orientador_id UUID REFERENCES orientador(id),
    tipo_orientacao VARCHAR(20) NOT NULL CHECK (tipo_orientacao IN ('ORIENTADOR', 'COORIENTADOR')),
    data_inicio DATE NOT NULL,
    data_fim DATE,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT unico_orientador_por_tcc UNIQUE (tcc_id, orientador_id)
);

-- ============================================
-- MÓDULO: DOCUMENTOS E ARQUIVOS
-- ============================================

CREATE TABLE tipo_documento (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    extensoes_permitidas TEXT[] DEFAULT ARRAY['.pdf', '.doc', '.docx'],
    tamanho_maximo_mb INTEGER DEFAULT 50,
    obrigatorio BOOLEAN DEFAULT false,
    ordem_exibicao INTEGER,
    ativo BOOLEAN DEFAULT true
);

CREATE TABLE documento (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tcc_id UUID REFERENCES tcc(id) ON DELETE CASCADE,
    tipo_documento_id UUID REFERENCES tipo_documento(id),
    
    nome_arquivo VARCHAR(255) NOT NULL,
    arquivo_url VARCHAR(1000) NOT NULL,
    tamanho_bytes BIGINT,
    hash_arquivo VARCHAR(64), -- SHA-256
    mime_type VARCHAR(100),
    
    versao INTEGER DEFAULT 1,
    versao_anterior_id UUID REFERENCES documento(id),
    
    status VARCHAR(50) DEFAULT 'PENDENTE' CHECK (status IN ('PENDENTE', 'APROVADO', 'REJEITADO', 'REVISAO')),
    comentarios TEXT,
    
    upload_por UUID REFERENCES usuario(id),
    upload_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- MÓDULO: BANCA EXAMINADORA
-- ============================================

CREATE TABLE banca (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tcc_id UUID REFERENCES tcc(id) ON DELETE CASCADE,
    tipo_banca VARCHAR(50) NOT NULL CHECK (tipo_banca IN ('QUALIFICACAO', 'DEFESA_FINAL')),
    
    data_agendada TIMESTAMP NOT NULL,
    local VARCHAR(255),
    formato VARCHAR(20) CHECK (formato IN ('PRESENCIAL', 'REMOTA', 'HIBRIDA')),
    link_reuniao VARCHAR(500),
    
    status VARCHAR(50) DEFAULT 'AGENDADA' CHECK (status IN (
        'AGENDADA', 'CONFIRMADA', 'EM_ANDAMENTO', 
        'CONCLUIDA', 'CANCELADA', 'REAGENDADA'
    )),
    
    ata_documento_id UUID REFERENCES documento(id),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE membro_banca (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    banca_id UUID REFERENCES banca(id) ON DELETE CASCADE,
    usuario_id UUID REFERENCES usuario(id),
    
    tipo_participacao VARCHAR(50) NOT NULL CHECK (tipo_participacao IN (
        'PRESIDENTE', 'ORIENTADOR', 'EXAMINADOR_INTERNO', 
        'EXAMINADOR_EXTERNO', 'SUPLENTE'
    )),
    
    instituicao_externa VARCHAR(255), -- Se examinador externo
    confirmado BOOLEAN DEFAULT false,
    presente BOOLEAN,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT unico_membro_banca UNIQUE (banca_id, usuario_id)
);

CREATE TABLE avaliacao (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    banca_id UUID REFERENCES banca(id) ON DELETE CASCADE,
    membro_banca_id UUID REFERENCES membro_banca(id),
    
    nota DECIMAL(4,2) CHECK (nota >= 0 AND nota <= 10),
    parecer TEXT,
    
    criterios_avaliacao JSONB, -- Estrutura customizável
    
    resultado VARCHAR(50) CHECK (resultado IN ('APROVADO', 'APROVADO_COM_RESSALVAS', 'REPROVADO')),
    recomendacoes TEXT,
    
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- MÓDULO: WORKFLOW E CRONOGRAMA
-- ============================================

CREATE TABLE template_cronograma (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    curso_id UUID REFERENCES curso(id),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE etapa_template (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    template_cronograma_id UUID REFERENCES template_cronograma(id) ON DELETE CASCADE,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    ordem INTEGER NOT NULL,
    duracao_dias INTEGER,
    obrigatoria BOOLEAN DEFAULT true,
    documentos_exigidos UUID[], -- Array de IDs de tipo_documento
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cronograma_tcc (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tcc_id UUID REFERENCES tcc(id) ON DELETE CASCADE,
    template_cronograma_id UUID REFERENCES template_cronograma(id),
    data_inicio DATE NOT NULL,
    data_fim_prevista DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE etapa_tcc (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cronograma_tcc_id UUID REFERENCES cronograma_tcc(id) ON DELETE CASCADE,
    etapa_template_id UUID REFERENCES etapa_template(id),
    
    nome VARCHAR(255) NOT NULL,
    ordem INTEGER NOT NULL,
    
    data_inicio_prevista DATE,
    data_fim_prevista DATE,
    data_inicio_real DATE,
    data_conclusao DATE,
    
    status VARCHAR(50) DEFAULT 'PENDENTE' CHECK (status IN (
        'PENDENTE', 'EM_ANDAMENTO', 'CONCLUIDA', 
        'ATRASADA', 'BLOQUEADA', 'CANCELADA'
    )),
    
    progresso_percentual INTEGER DEFAULT 0 CHECK (progresso_percentual >= 0 AND progresso_percentual <= 100),
    
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- MÓDULO: COMUNICAÇÃO E NOTIFICAÇÕES
-- ============================================

CREATE TABLE notificacao (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID REFERENCES usuario(id) ON DELETE CASCADE,
    
    tipo VARCHAR(50) NOT NULL CHECK (tipo IN (
        'INFO', 'ALERTA', 'PRAZO', 'APROVACAO', 
        'REJEICAO', 'CONVITE', 'LEMBRETE'
    )),
    
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    
    link_referencia VARCHAR(500),
    entidade_tipo VARCHAR(50), -- 'TCC', 'BANCA', 'DOCUMENTO'
    entidade_id UUID,
    
    lida BOOLEAN DEFAULT false,
    data_leitura TIMESTAMP,
    
    canal VARCHAR(20) DEFAULT 'SISTEMA' CHECK (canal IN ('SISTEMA', 'EMAIL', 'SMS', 'PUSH')),
    enviado BOOLEAN DEFAULT false,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE mensagem (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tcc_id UUID REFERENCES tcc(id) ON DELETE CASCADE,
    remetente_id UUID REFERENCES usuario(id),
    
    assunto VARCHAR(255),
    conteudo TEXT NOT NULL,
    
    mensagem_pai_id UUID REFERENCES mensagem(id), -- Para threads
    
    anexos JSONB, -- Array de referências a arquivos
    
    lida BOOLEAN DEFAULT false,
    data_leitura TIMESTAMP,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE mensagem_destinatario (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    mensagem_id UUID REFERENCES mensagem(id) ON DELETE CASCADE,
    usuario_id UUID REFERENCES usuario(id),
    lida BOOLEAN DEFAULT false,
    data_leitura TIMESTAMP
);

-- ============================================
-- MÓDULO: AUDITORIA E LOG
-- ============================================

CREATE TABLE auditoria (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID REFERENCES usuario(id),
    
    acao VARCHAR(50) NOT NULL, -- 'CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT'
    entidade VARCHAR(50) NOT NULL, -- Nome da tabela afetada
    entidade_id UUID,
    
    dados_anteriores JSONB,
    dados_novos JSONB,
    
    ip_address INET,
    user_agent TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- MÓDULO: CONFIGURAÇÕES DO SISTEMA
-- ============================================

CREATE TABLE configuracao_sistema (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    instituicao_id UUID REFERENCES instituicao(id),
    chave VARCHAR(100) NOT NULL,
    valor JSONB NOT NULL,
    descricao TEXT,
    tipo_dado VARCHAR(20) CHECK (tipo_dado IN ('STRING', 'INTEGER', 'BOOLEAN', 'JSON')),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT unico_config_instituicao UNIQUE (instituicao_id, chave)
);

-- ============================================
-- ÍNDICES PARA OTIMIZAÇÃO
-- ============================================

-- Índices de busca frequente
CREATE INDEX idx_usuario_email ON usuario(email);
CREATE INDEX idx_usuario_tipo ON usuario(tipo_usuario);
CREATE INDEX idx_usuario_instituicao ON usuario(instituicao_id);

CREATE INDEX idx_tcc_aluno ON tcc(aluno_id);
CREATE INDEX idx_tcc_status ON tcc(status);
CREATE INDEX idx_tcc_curso ON tcc(curso_id);
CREATE INDEX idx_tcc_deleted ON tcc(deleted_at) WHERE deleted_at IS NULL;

CREATE INDEX idx_documento_tcc ON documento(tcc_id);
CREATE INDEX idx_documento_tipo ON documento(tipo_documento_id);

CREATE INDEX idx_banca_tcc ON banca(tcc_id);
CREATE INDEX idx_banca_data ON banca(data_agendada);

CREATE INDEX idx_notificacao_usuario ON notificacao(usuario_id);
CREATE INDEX idx_notificacao_lida ON notificacao(lida) WHERE lida = false;

CREATE INDEX idx_auditoria_usuario ON auditoria(usuario_id);
CREATE INDEX idx_auditoria_entidade ON auditoria(entidade, entidade_id);
CREATE INDEX idx_auditoria_data ON auditoria(created_at);

-- Índices de texto completo
CREATE INDEX idx_tcc_titulo_busca ON tcc USING gin(to_tsvector('portuguese', titulo));
CREATE INDEX idx_tcc_palavras_chave ON tcc USING gin(palavras_chave);

-- ============================================
-- TRIGGERS PARA AUTOMAÇÃO
-- ============================================

-- Trigger para atualizar updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_usuario_timestamp BEFORE UPDATE ON usuario
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_tcc_timestamp BEFORE UPDATE ON tcc
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Trigger para atualizar contador de orientandos
CREATE OR REPLACE FUNCTION atualizar_contador_orientandos()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' AND NEW.ativo = true AND NEW.tipo_orientacao = 'ORIENTADOR' THEN
        UPDATE orientador 
        SET orientandos_atuais = orientandos_atuais + 1
        WHERE id = NEW.orientador_id;
    ELSIF TG_OP = 'DELETE' AND OLD.tipo_orientacao = 'ORIENTADOR' THEN
        UPDATE orientador 
        SET orientandos_atuais = orientandos_atuais - 1
        WHERE id = OLD.orientador_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_contador_orientandos
AFTER INSERT OR DELETE ON orientacao
FOR EACH ROW EXECUTE FUNCTION atualizar_contador_orientandos();

-- Trigger para log de auditoria
CREATE OR REPLACE FUNCTION log_auditoria()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'DELETE' THEN
        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, dados_anteriores)
        VALUES (current_setting('app.current_user_id', true)::UUID, 'DELETE', TG_TABLE_NAME, OLD.id, row_to_json(OLD));
        RETURN OLD;
    ELSIF TG_OP = 'UPDATE' THEN
        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, dados_anteriores, dados_novos)
        VALUES (current_setting('app.current_user_id', true)::UUID, 'UPDATE', TG_TABLE_NAME, NEW.id, row_to_json(OLD), row_to_json(NEW));
        RETURN NEW;
    ELSIF TG_OP = 'INSERT' THEN
        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, dados_novos)
        VALUES (current_setting('app.current_user_id', true)::UUID, 'CREATE', TG_TABLE_NAME, NEW.id, row_to_json(NEW));
        RETURN NEW;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- Aplicar auditoria nas tabelas principais
CREATE TRIGGER audit_tcc AFTER INSERT OR UPDATE OR DELETE ON tcc
    FOR EACH ROW EXECUTE FUNCTION log_auditoria();

CREATE TRIGGER audit_documento AFTER INSERT OR UPDATE OR DELETE ON documento
    FOR EACH ROW EXECUTE FUNCTION log_auditoria();
```
