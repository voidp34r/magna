#################
Transmagna WebApp
#################

Transmagna WebApp é a nova aplicação interna da Transmagna Transportes, os seus objetivos são, automatização de processos, melhoria na comunicação entre colaboradores e departamentos, centralização de informações essenciais e a geração de relatórios customizados de outros sistemas. A meta do sistema é substituir a atual Extranet da empresa e ser uma aplicação que produza informações para atuação estratégica, tática e operacional na evolução do negócio.

*********************
Requisitos de sistema
*********************

- PHP 5.6+
- Oracle 10g
- PDO_OCI

*******************
Estrutura de pastas
*******************

-  application: Configurações, módulos, funções genéricas e o `HMVC <https://bitbucket.org/wiredesignz/codeigniter-modular-extensions-hmvc>`_
-  assets: Arquivos de CSS, JS e imagens criadas pela equipe de desenvolvimento
-  bower_components: Bibliotecas instaladas via Bower
-  system: Core do framework `Codeigniter <http://www.codeigniter.com/user_guide>`_
-  upload: Arquivos enviados pelos usuários e armazenados no servidor

*******************
Pasta *application*
*******************

A pasta da aplicação é responsável por conter todo o desenvolvimento realizado pela equipe, para que seja mantida organizada, algumas regras foram estabelecidas:

-  config: só poderá ter arquivos criados se estes vierem de bibliotecas de terceiros
-  controllers: permanecerá sempre vazia
-  helpers: irá conter arquivos com funções auxiliares de diversas naturezas, por exemplo, data_helper para tratar funções de data, texto_helper para formatação de texto, e assim por diante.
-  libraries: deve conter classes que realizem comunicação com aplicações de terceiros ou classes que estendem classes nativas do Codeigniter (Ex.: MY_Form_validation)
-  models: só poderá conter classes de modelo que são usadas por diversos módulos, por padrão, o nome dos arquivos devem ter o prefixo "Geral"
-  third_party: contém a biblioteca de HMVC do `wiredesignz <https://bitbucket.org/wiredesignz>`_
- views: contém arquivos de template para renderização das views