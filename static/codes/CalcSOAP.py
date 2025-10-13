#!/usr/bin/python
from SOAPpy import SOAPServer

def calcula(op1,op2,operacao):
        if operacao == '+':
                return op1 + op2
        if operacao == '-':
                return op1 - op2
        if operacao == '*':
                return op1 * op2
        if operacao == '/':
                return op1 / op2
server = SOAPServer(('localhost',8081))
server.registerFunction(calcula)
server.serve_forever()