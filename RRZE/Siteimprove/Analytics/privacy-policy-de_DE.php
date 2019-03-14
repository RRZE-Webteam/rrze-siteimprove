<?php

namespace RRZE\Siteimprove\Analytics;

defined('ABSPATH') || exit;
?>
<h2>Nutzung von Siteimprove Analytics</h2>
<h3>Beschreibung und Umfang der Datenverarbeitung</h3>
<p>Diese Website benutzt Siteimprove Analytics, einen Webanalysedienst, der von Siteimprove zur Verfügung gestellt wird. Siteimprove Analytics nutzt „Cookies“ - Textdateien, die auf Ihrem Rechner oder Smartphone gespeichert werden, um der FAU<b> </b>dabei zu helfen zu analysieren, wie Besucher die Website benutzen. Die Information, die durch die Cookies über die Website-Nutzung der Besucher erstellt wird, wird von Siteimprove auf Servern in Dänemark gespeichert und verarbeitet.</p>
<p>IP-Adressen werden vollständig anonymisiert bevor erhobene Daten über die Siteimprove Suite für die FAU einsehbar sind. Eine Umkehrung der Anonymisierung der IP-Adressen und eine Zuordnung der IP-Adressen zu erhobenen Daten ist nicht möglich.</p>
<p>Die FAU wird diese Informationen nutzen, um das Benutzerverhalten seiner Websitebesucher auszuwerten, Berichte darüber zu erstellen und schlussendlich um das Websiteerlebnis für seine Besucher zu verbessern. Siteimprove wird diese Informationen nicht an Dritte weitergeben oder sie für Marketing- oder Werbezwecke jeglicher Art benützen.</p>


<p>Diese Cookies von Siteimprove werden auf dieser Website eingesetzt:</p>

<ul>
 	<li><strong>Name des Cookies</strong>: <b>nmstat </b>
<ul>
 	<li><strong>Typ</strong>: Persistent – läuft nach 1000 Tagen ab</li>
 	<li><strong>Über das Cookie</strong>: Dieses Cookie wird genutzt, um das Verhalten der Besucher auf der Website festzuhalten. Es wird genutzt um Statistiken über die Websitenutzung zu sammeln, wie zum Beispiel wann der Besucher die Website zuletzt besucht hat. Das Cookie enthält keine personenbezogenen Daten und wird einzig für die Websiteanalyse eingesetzt.</li>
</ul>
</li>
 	<li><strong>Name des Cookies</strong>: <b>siteimproveses </b>
<ul>
 	<li><strong>Typ</strong>: Sitzungscookie</li>
 	<li><strong>Über das Cookie</strong>: Dieses Cookie wird dafür eingesetzt, um die Abfolge an Seiten zu verfolgen, die ein Besucher im Laufe seines Besuchs auf der Website ansieht. Das Cookie enthält keine personenbezogenen Daten und wird einzig für die Websiteanalyse eingesetzt.</li>
</ul>
</li>
</ul>
<p>Durch die Benutzung dieser Website erklärt sich der Besucher mit der Verarbeitung seiner bzw. ihrer Daten zu den weiter oben beschriebenen Zwecken einverstanden.</p>


<h3>Rechtsgrundlage für die Verarbeitung personenbezogener Daten</h3>
<p>Die Rechtsgrundlage für die Verarbeitung personenbezogener Daten unter Verwendung von Cookies ist Art. 6 Abs. 1 e DSGVO in Verbindung mit Art. 4 BayDSG,insbesondere der Aufgaben aus § 15 Abs. 3 TMG und Art. 10 BayHSchG.</p>

<h3>Widerspruchs- und Beseitigungsmöglichkeit</h3>
<p>Sie können der Sammlung Ihrer Daten durch Siteimprove Analytics widersprechen, indem Sie auf den folgenden Link klicken. Dadurch wird ein Opt-Out Cookie gesetzt, der eine zukünftige Sammlung Ihrer Daten verhindert, wenn Sie diese Website besuchen.</p>

<button class="btn btn-primary" id="szOptOutIn">Opt out</button>
<script type="text/javascript">
window.onload = function () {
    function changeOptSetting(obj, txtOptIn, txtOptOut) {
        if(_sz){
            obj.innerHTML=_sz.tracking()?txtOptOut:txtOptIn;
            obj.onclick = function() {
                _sz.push(['notrack', _sz.tracking()]);
                obj.innerHTML=_sz.tracking()?txtOptOut:txtOptIn;
            };
        }
    }
    changeOptSetting(document.getElementById("szOptOutIn"), "Opt In", "Opt Out");
}
</script>
