<div class="snail-services">
	<h1 class="text-center mt-5">Services</h1>
	<div class="text-center">
		<p class="m-3">Snappysnail is a web development company based near the lake of Como. We provide web development services aimed to small businesses and other web agencies.</p>
		<p class="mb-3">Please contact us if you need a quote, or just to say hi: <livewire:button url="{{ url('contact') }}" label="Contact" /></p>
	</div>
	<div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
		<livewire:service-card 
			title="Website development"
			image_path="{{ asset('img/services/services-0.jpg') }}"
			description="If you are a small company or a sole trader and you need your professional website, we are glad to help!"
		/>
		<livewire:service-card 
			title="Outsourcing"
			image_path="{{ asset('img/services/services-1.jpg') }}"
			description="If you are a web agency and need some extra development power, let's discuss it! You can find more details on my CV."
		/>
		<livewire:service-card 
			title="Landing pages"
			image_path="{{ asset('img/services/services-2.jpg') }}"
			description="Do you just need a landing page for your campaign? Check!"
		/>
		<livewire:service-card 
			title="Consulting"
			image_path="{{ asset('img/services/services-3.jpg') }}"
			description="We can also give you any professional advice related to web development."
		/>
	</div>
</div>