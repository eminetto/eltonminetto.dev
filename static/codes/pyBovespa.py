#!/usr/bin/python
# -*- coding: utf-8 -*-
# Elton Luís Minetto
import urllib
from xml.dom import minidom
from time import sleep
from os import system

#adicionar as acoes aqui
#formato ACAO: [num_acoes,valor_compra,data_compra]
acoes = {
	'PETR4':[100,41.55,'20/04/2008'],
}
	
def atualiza(acoes):
	system('clear')
	url = 'http://www.bovespa.com.br/Mercado/RendaVariavel/InfoPregao/ExecutaAcaoAjax.asp?CodigoPapel='
	for i in acoes:
		url += '|'+i
	f = urllib.urlopen(url)
	xml = f.read()
	xmldoc = minidom.parseString(xml)
	papeis = xmldoc.getElementsByTagName('Papel')
	#cabecalho
	print 'Ação\tValor de Compra\tData da Compra\tQtd\tAtual\tDiferença R$\tDiferença %\tData de Atualização'
	
	total_compra = 0.0
	total_dif_reais = 0.0
	for i in papeis:
		codigo = i.attributes['Codigo'].value
		valor_compra = acoes[codigo][1]
		qtd_acoes = acoes[codigo][0]
		data_compra = acoes[codigo][2]
		valor_atual = i.attributes['Ultimo'].value.replace(',','.')	
		data_atual = i.attributes['Data'].value
		
		diferenca_reais = (float(valor_atual) * qtd_acoes) - (valor_compra * qtd_acoes)
		diferenca_perc = (diferenca_reais*100)/(valor_compra * qtd_acoes)
		total_compra += valor_compra * qtd_acoes
		total_dif_reais += diferenca_reais
		
		print '%s\t%02f\t%s\t%d\t%s\t%02f\t%02f\t%s' % (codigo,valor_compra,data_compra,qtd_acoes,valor_atual,diferenca_reais,diferenca_perc,data_atual)
	
	print 'Total de Compra:%02f' % total_compra
	print 'Total da Diferença em Reais:%02f' % total_dif_reais
	total_dif_perc = (total_dif_reais * 100)/total_compra
	print 'Total da Diferença em Percentual:%02f' % total_dif_perc
	sleep(1200)

while 1:
	atualiza(acoes)
