#!/bin/bash

# Caminho da pasta onde estão os arquivos de regras
INSTRUCOES="./roo-instructions"
CHECKPOINT="./roo-instructions/control.clinecheckpoint"

echo "🔍 Verificando fases disponíveis..."
fases=($(ls $INSTRUCOES/fase-*.clinerules | sort))

if [ ${#fases[@]} -eq 0 ]; then
  echo "⚠️  Nenhuma fase encontrada em $INSTRUCOES."
  exit 1
fi

echo "📌 Fases detectadas:"
for i in "${!fases[@]}"; do
  nome=$(basename ${fases[$i]})
  echo "  [$((i+1))] $nome"
done

echo ""
read -p "Digite o número da fase que deseja aplicar com o Roo Code: " escolha

index=$((escolha-1))
fase_escolhida="${fases[$index]}"

if [ ! -f "$fase_escolhida" ]; then
  echo "❌ Fase inválida ou arquivo não encontrado."
  exit 1
fi

echo ""
echo "🚀 Envie agora o seguinte arquivo para o Roo Code:"
echo ""
echo "📄 ${fase_escolhida}"
echo ""
echo "✅ Após a execução, atualize manualmente o arquivo:"
echo "📄 $CHECKPOINT"
echo ""
echo "⚠️ Não envie mais de um arquivo .clinerules por vez ao Roo Code para evitar perda de contexto."
