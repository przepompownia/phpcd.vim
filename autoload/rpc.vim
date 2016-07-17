function! rpc#request(...) " {{{
	if has('nvim')
		return call('rpcrequest', a:000)
	else
		let channel = job_getchannel(a:1)
		let request = [0, 1, a:2, a:000[2:]]
		let [type, msgid, error, response] = json_decode(ch_evalraw(channel, json_encode(request)."\n"))
		if error
			throw error
		end
		return response
	end
endfunction " }}}

function! rpc#notify(...) " {{{
	if has('nvim')
		return call('rpcnotify', a:000)
	else
		let channel = job_getchannel(a:1)
		let notice = [2, 1, a:2, a:000[2:]]
		call ch_sendraw(channel, json_encode(notice)."\n")
	end
endfunction " }}}

function! rpc#start(...) " {{{
	if has('nvim')
		return call('rpcstart', a:000)
	else
		let args = a:2
		call insert(args, a:1, 0)
		return job_start(args)
	end
endfunction " }}}

function! rpc#stop(...) " {{{
	if has('nvim')
		return call('rpcstop', a:000)
	else
		return job_stop(a:1)
	end
endfunction " }}}

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
