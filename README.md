Bicoin Trade BOT (btc-e)
==============

Um simples bot para trades no BTC-E!





Uso:
==============

Para utilizar este bot, você precisa alterar as chaves de $api e $key para a chave cedida no btc-e

https://btc-e.com/profile#api_keys

Obs: Não esqueça de liberar as permissões marcando as caixas INFO e TRADE


Alteração:
==============

Neste sistema, há algumas variáveis para alterar durante o uso do aplicativo, segue a descrição de cada uma:


$tradeusd

Esta variável, predefinida para 10. Representa quantos reais inicias devem ser feitos para compra de cada Bitcoin. Lembrando que você precisa ter esse valor na carteira!


$fee

Esta, é o valor médio cobrado pela exchange em cada transação. Quanto maior for este valor, mais lucro o programa vai visar. Porém com valores muito altos, a taxa de sucesos na conclusao das ordens é menor. 


$tempo

Este é o tempo predefinido em MINUTOS que indica quanto tempo uma ordem de compra deve ficar aberto. Isto evita problemas com ordens que nunca fecham.



Dicas:
==============

Procure não utilizar o projeto em horários com pouco movimentação na exchang ou em periodos com muita ALTA ou muita BAIXA do Bitcoin. Isto pode fazer suas ordens demorarem mais para fechar.


De forma alguma, altere, cancele ou abra novas ordens enquanto o programa estiver rodando. Caso queira cancelar uma ordem manualmente, ou criar uma ordem manualmente, feche o programa.

Tenha paciência, algumas ordens podem levar vários minutos para fechar. E o lucro nem sempre é grande suficiente, este aplicativo foi criado para rodar várias horas a fim de obter lucro.
