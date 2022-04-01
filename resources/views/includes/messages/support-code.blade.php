@if(\Carbon\Carbon::parse('2022-04-01T06:58')->isPast() && \Carbon\Carbon::parse('2022-04-01T23:59')->isFuture())
    <div class="alert alert-danger mt-3 text-trwl">
        <h4>Kennst du schon unsere Hotline?</h4>
        Ab heute kannst du deine Checkins telefonisch unter <a href="tel:+4921528079589">+49 2152 807 9589</a> tätigen!<br>
        Dafür benötigst du deinen Supportcode, den du hier abrufen kannst:
        <a href="javascript:void(0)" onclick="alert('{{auth()->user()->support_code}}')">Supportcode</a><br>
        Es gibt auch einen maschinellen Service, für den du dich auf <a target="_blank" href="https://handvermittlung.traewelling.de">handvermittlung.traewelling.de</a> registrieren musst.
        Alle weiteren Infos hierzu findest du in unserem <a href="https://blog.traewelling.de">Blog</a>.
    </div>
@endif
