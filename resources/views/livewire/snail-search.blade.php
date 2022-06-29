<div>
	<div class="md:max-w-6xl mx-auto my-9 px-6">
	    <div class="snail-search">
	    	<label for="searchField" class="hidden">Search</label>
	        <input type="text" class="mt-1 block w-full rounded-md bg-white border-transparent focus:border-gray-200 focus:bg-white focus:ring-0 shadow-lg" wire:model.debounce.500ms="searchTerms" id="searchField">
	    </div>
	</div>
	<div class="md:max-w-6xl mx-auto text-gray-400 text-center">
	    <div class="snail-explanation">
	        <p class="my-9 px-6 snail-1">The speed of a snail is 0.013 m/s</p>
	        <p class="my-9 px-6 snail-2">This search bar is loading much much faster</p>
	        <p class="my-9 px-6 snail-3">The perception of speed is relative</p>
	    </div>
	</div>
	<div class="md:max-w-6xl mx-auto my-9 px-6">
		@if($no_results ?? false)
			<p class="my-9 text-gray-500">Sorry, no results for your query. Suggestions? <a href="{{ url('portfolio') }}">portfolio</a> or <a href="{{ url('curriculum') }}">curriculum</a> or maybe <a href="{{ url('contact') }}">contact</a></p>
		@elseif($too_short ?? false)
			<p class="my-9 text-gray-500">Please go on...</p>
		@else
			@if($suggestions)
				<ul>
			        @foreach($suggestions as $suggestion)
			            <li class="my-9">
			            	@if($suggestion->path)
			            		<a href="{{ url($suggestion->path) }}">{{ $suggestion->description }}</a>
			            	@elseif($suggestion->view)
			            		@include($suggestion->view)
			            	@endif
			            </li>
			        @endforeach
			    </ul>
			@endif
		@endif
	</div>
</div>