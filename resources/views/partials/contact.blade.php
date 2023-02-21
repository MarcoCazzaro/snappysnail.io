<div class="snail-contact">
    <h1 class="text-center mt-5">Contact</h1>
    <div class="text-center dark:text-gray-400">
        <p class="m-3">To contact me, please send an email to info [at] snappysnail.io</p>
        <div class="p-8 flex justify-center">
            {!!
                \QrCode::size(300)
                    ->format('svg')
                    ->margin('13px')
                    ->generate('BEGIN:VCARD
VERSION:4.0
FN:Marco Cazzaro
N:Cazzaro;Marco
ORG:Snappysnail
ADR;type=WORK:;;Via IV Novembre 9;Lambrugo;CO;22045;ITALY
URL:https://snappysnail.io
EMAIL;TYPE=work:info@snappysnail.io
LOGO;MEDIATYPE=image/png:https://snappysnail.io/img/snappysnail-logo.png
CATEGORIES:web,developer
TZ:Europe/Rome
END:VCARD');
            !!}
        </div>
    </div>
</div>