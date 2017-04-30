vimWithPHPCDOnly()
{
	scriptDir=$(dirname $(readlink -f "${BASH_SOURCE[0]}"))
	phpcdDir=${scriptDir}/../..
	vimExecuable=${1:-$(which nvim)}
	shift

	$vimExecuable -u vimrc -U NONE -N \
		--cmd set\ rtp+=${phpcdDir} \
		"$@"
}
