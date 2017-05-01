vimWithPHPCDOnly()
{
	if [[ $# < 2 ]]; then
		echo Two arguments are required: vim executable and the file path.
		return 1
	fi

	scriptDir=$(dirname $(readlink -f "${BASH_SOURCE[0]}"))
	phpcdDir=${scriptDir}/../../..
	vimExecuable=${1}
	shift

	$vimExecuable -u NONE -U NONE -N \
		--cmd "set rtp+=${phpcdDir}" \
		--cmd "filetype plugin on" \
		--cmd "syntax on" \
		--cmd "set virtualedit+=onemore" \
		--cmd "source ${phpcdDir}/plugin/phpcd.vim" \
		"$@"
}
