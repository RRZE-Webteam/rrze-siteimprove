<?php

namespace RRZE\Siteimprove\Analytics;

defined('ABSPATH') || exit;
?>
<h2>Usage of Siteimprove Analytics</h2>
<h3>Description and scope of data processing</h3>
<p>This website uses Siteimprove Analytics, a web analytics service provided by Siteimprove. Siteimprove Analytics uses „cookies“, which are text files placed on your computer, to help the FAU analyze how visitors use the site. The information generated by the cookies about the visitors’ use of the website will be stored and processed by Siteimprove on servers in Denmark.</p>
<p>IP addresses are anonymized irreversibly before data is made available in the Siteimprove Analytics or Intelligence Suite for the FAU.</p>
<p>The FAU will use this information for evaluating the visitors’ use of the website, compiling reports on website activity, and ultimately for improving the website experience for its visitors. Siteimprove will not transmit this information to third parties or use it for any marketing or advertising purposes.</p>


<p>These are the cookies used by Siteimprove on this website:</p>

<ul>
 	<li><strong>Cookie name</strong>: <code>nmstat</code>
<ul>
 	<li><strong>Type</strong>: Persistent - expires after 1000 days</li>
 	<li><strong>About</strong>: This cookie is used to help record visitors' use of the website. It is used to collect statistics about site usage such as when the visitor last visited the site. The cookie contains no personal information and is used only for web analytics.</li>
</ul>
</li>
 	<li><strong>Cookie name</strong>: <code>siteimproveses</code>
<ul>
 	<li><strong>Type</strong>: Session cookie</li>
 	<li><strong>About</strong>: This cookie is used purely to track the sequence of pages a visitor looks at during a visit to the site.</li>
</ul>
</li>
</ul>
<p>By using this website, the visitor consents to the processing of data about him/her by Siteimprove in the manner and for the purposes set out above.</p>


<h3>Legal basis for the processing of personal data</h3>
<p>The legal basis for the processing of personal data using cookies is Art. 6 (1) (e) GDPR in relation with Art. 4 BayDSG, especially the specification under § 15 (3) TMG and Art. 10 BayHSchG.</p>

<h3>Possibilities of objection and deletion</h3>
<p>You can prevent the collection of your data by Siteimprove Analytics by clicking the following link. An Opt-Out-Cookie will be set that will prevent future collection of your data when visiting this website:</p>

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
