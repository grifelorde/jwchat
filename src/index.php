<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title><l>Welcome to JWChat</l> - the Jabber Web Chat</title>
    
    <meta name="description" content="A free, web based instant messaging client for the XMPP aka Jabber network. Through gateways it allows to connect to foreign networks like AIM, ICQ, Yahoo! and MSN. It includes support for multi user conferences (groupchats or chat rooms)">
    <meta name="keywords" content="Jabber, XMPP, web based, AJAX, instant messaging, browser, MUC, chat rooms, conferences, chat, multi user, JavaScript, HTML, client, HTTP Binding, BOSH, HTTP Polling, ejabberd, SSL, apache, free"> 
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <meta name="verify-v1" content="xlVccy/b29cMNfzNj7zRo6zOX/W/IPs2vgdv714aGTE=" />

    <script src="config.js" language="JavaScript1.2"></script>
    <script src="browsercheck.js" language="JavaScript1.2"></script>
    <script src="shared.js" language="JavaScript1.2"></script>
    <script src="switchStyle.js"></script>
    <script language="JavaScript">
<!--

 /*
  * JWChat, a web based jabber client
  * Copyright (C) 2003-2004 Stefan Strigler <steve@zeank.in-berlin.de>
  *
  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License
  * as published by the Free Software Foundation; either version 2
  * of the License, or (at your option) any later version.
  *
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
  *
  * Please visit http://jwchat.sourceforge.net for more information!
  */

var jid, pass, register, prio, connect_host, connect_port, connect_secure;
var jwchats = new Array();

var JABBERSERVER;
var HTTPBASE;
var BACKEND_TYPE;

/* check if user want's to register new
 * account and things */    
function loginCheck(form) { 
  if (form.jid.value == '') {
    alert(loc("You need to supply a username"));
    return false;
  }

  if (form.pass.value == '') {
    alert(loc("You need to supply a password"));
    return false;
  }

  if (document.getElementById('tr_server').style.display != 'none') {
    var val = document.getElementById('server').value;
    if (val == '') {
      alert(loc("You need to supply a jabber server"));
      return false;
    }

    JABBERSERVER = val;
  }

  jid = form.jid.value + "@" + JABBERSERVER + "/";
  if (form.res.value != '')
    jid += form.res.value;
  else
    jid += DEFAULTRESOURCE;

  if(!isValidJID(jid))
    return false;

  if (jwchats[jid] && !jwchats[jid].closed) {
    jwchats[jid].focus();
    return false;
  }

  pass = form.pass.value;
  register = form.register.checked;

  prio = form.prio[form.prio.selectedIndex].value;

  connect_port = form.connect_port.value;
  connect_host = form.connect_host.value;
  connect_secure = form.connect_secure.checked;

  jwchats[jid] = window.open('jwchat.html',makeWindowName(jid),'width=180,height=390,resizable=yes');

  return false;
}

function toggleMoreOpts(show) {
  if (show) {
    document.getElementById('showMoreOpts').style.display = 'none';
    document.getElementById('showLessOpts').style.display = '';
  } else {
    document.getElementById('showLessOpts').style.display = 'none';
    document.getElementById('showMoreOpts').style.display = '';
  }

  var rows = document.getElementById('lTable').getElementsByTagName('TBODY').item(0).getElementsByTagName('TR');

  for (var i=0; i<rows.length; i++) {
    if (rows[i].className == 'moreOpts') {
      if (show)
	rows[i].style.display = '';
      else
	rows[i].style.display = 'none';
    }
  }
  return false;
}

function serverSelected() {
  var oSel = document.getElementById('server');
  var servers_allowed = BACKENDS[bs.selectedIndex].servers_allowed;

  // TODO ...
  
  /* change format of servers_allowed to be able to associate connect 
   * host information to it 
   */
}

function backendSelected() {
  var bs = document.getElementById('backend_selector');
  var servers_allowed, default_server;
  if (bs) {
    servers_allowed = BACKENDS[bs.selectedIndex].servers_allowed;
    default_server = BACKENDS[bs.selectedIndex].default_server;
    if (BACKENDS[bs.selectedIndex].description)
      document.getElementById('backend_description').innerHTML = BACKENDS[bs.selectedIndex].description;
    HTTPBASE = BACKENDS[bs.selectedIndex].httpbase;
    BACKEND_TYPE = BACKENDS[bs.selectedIndex].type;
  }	else {
    servers_allowed = BACKENDS[0].servers_allowed;
    default_server = BACKENDS[0].default_server;
    HTTPBASE = BACKENDS[0].httpbase;
    BACKEND_TYPE = BACKENDS[0].type;
  }
  
  if (!servers_allowed
      || servers_allowed.length == 0) 
    { // allow any
      var tr_server = document.getElementById('tr_server');
      
      var input = document.createElement('input');
      input.setAttribute("type","text");
      input.setAttribute("id","server");
      input.setAttribute("name","server");
      input.setAttribute("tabindex","2");
      input.className = 'input_text';
      
      if (default_server)
	input.setAttribute("value",default_server);
      
      var td = tr_server.getElementsByTagName('td').item(0);
      for (var i=0; i<td.childNodes.length; i++)
	td.removeChild(td.childNodes.item(i));
      
      td.appendChild(input);
      
      tr_server.style.display = ''; 
      
      document.getElementById('connect_port').disabled = false;
      document.getElementById('connect_host').disabled = false;
      document.getElementById('connect_secure').disabled = false;
    }
  else if (servers_allowed.length == 1) {
    document.getElementById('tr_server').style.display = 'none';
    JABBERSERVER = servers_allowed[0];
    document.getElementById('connect_port').disabled = true;
    document.getElementById('connect_host').disabled = true;
    document.getElementById('connect_secure').disabled = true;
  } else { // create selectbox
    var tr_server = document.getElementById('tr_server');
    
    var oSelect = document.createElement('select');
    oSelect.setAttribute('id','server');
    oSelect.setAttribute('name','server');
    oSelect.setAttribute('tabindex',"2");
    oSelect.onchange = serverSelected;
    
    var td = tr_server.getElementsByTagName('td').item(0);
    for (var i=0; i<td.childNodes.length; i++)
      td.removeChild(td.childNodes.item(i));
    
    td.appendChild(oSelect);
    
  for (var i=0; i<servers_allowed.length; i++) {
    if (typeof(servers_allowed[i]) == 'undefined')
continue;
    oSelect.options.add(new Option(servers_allowed[i],servers_allowed[i]));
  }
  
  tr_server.style.display = ''; 
  document.getElementById('connect_port').disabled = true;
  document.getElementById('connect_host').disabled = true;
  document.getElementById('connect_secure').disabled = true;
}
}

function init() {
var welcome = loc("Welcome to JWChat at [_1]", SITENAME);
document.title = welcome;
document.getElementById("welcomeh1").innerHTML = welcome;

// create backend chooser - if any
if (typeof(BACKENDS) == 'undefined' || BACKENDS.length == 0) {
  // ...
} else if (BACKENDS.length == 1) {
  backendSelected();
} else {
  // create chooser
  var oSelect = document.createElement('select');
  oSelect.setAttribute('id','backend_selector');
  oSelect.setAttribute('name','backend');
  oSelect.setAttribute('tabindex',"1");
  oSelect.onchange = backendSelected;

  var tr = document.createElement('tr');
  var td = tr.appendChild(document.createElement('th'));
  var label = td.appendChild(document.createElement('label'));
  label.setAttribute('for','backend_selector');
  label.appendChild(document.createTextNode(loc("Choose Backend")));
  
  tr.appendChild(document.createElement('td')).appendChild(oSelect);
  
  var tr_server = document.getElementById('tr_server');
  tr_server.parentNode.insertBefore(tr,tr_server);
  
  tr = document.createElement('tr');
  td = tr.appendChild(document.createElement('td'));
  td = document.createElement('td');
  td.setAttribute('id','backend_description');
  td.className= 'desc';
  tr.appendChild(td);

  tr_server.parentNode.insertBefore(tr,tr_server);

  for (var i=0; i<BACKENDS.length; i++) {
    if (typeof(BACKENDS[i]) == 'undefined')
continue;
    var oOption =  new Option(BACKENDS[i].name,BACKENDS[i].httpbase);
    oOption.setAttribute('description',BACKENDS[i].description);
    oSelect.options[i] = oOption;
  }
  
  backendSelected();
}
document.forms[0].jid.focus();
document.getElementById('chars_prohibited').innerHTML = prohibited;
if (typeof(DEFAULTRESOURCE) != 'undefined' && DEFAULTRESOURCE)
  document.forms[0].res.value = DEFAULTRESOURCE;

document.getElementById('login_button').disabled = false;
}


onload = init;
//-->
  </script>

<!-- flattr button config -->
<script type="text/javascript">
/* <![CDATA[ */
  (function() {
            var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
            s.type = 'text/javascript';
            s.async = true;
            s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
            t.parentNode.insertBefore(s, t);
  })();
/* ]]> */
</script>

  <style type="text/css">
/*<![CDATA[*/
    body {
    color: #2a3847;
    background-color: white;
    }

    th {
    font-size: 0.8em;
    text-align: right;
    white-space: nowrap;
    }

    a { color: #2a3847; } 
    
    h1 { 
    font-size: 1.4em; 
    margin-top:0px; 
    margin-bottom: 0px; 
    }
    
    h2 { padding-top: 0px; margin-top: 0px; }
    
    h3 {
    border-bottom: 1px solid #2a3847;
    margin-bottom: 0px;

    font-style: normal;
    font-variant: small-caps;
    
    text-align: right;
    }
    
    input.input_text {
    border: 1px solid #2a3847;
    }
    
    input:focus, input:hover {
    background-color: #f9fae1;
    }
    
    .toggleOpts { cursor: pointer; }
    
    .desc {
    font-size: 0.65em;
    }
    
    .form_spacer {
    padding-top: 20px;
    }
    
    #td_top {
    padding-top: 20px;
    }
    #td_form {
    padding-top: 20px;
    }
    #td_bottom {
    padding: 4px;
    font-size:8pt; 
    border-top:1px solid #2a3847;
    }
    #lTable {
    padding: 8px;
    
    border: 2px solid #2a3847;
    -moz-border-radius: 8px;
    
    background-color: #81addc;
    }
#featured { padding:25px; margin:25px; text-align: left; }
#featured div { margin-left: -20px; margin-top: -20px; padding-bottom: 5px; font-size:0.8em; }
#featured ul { list-style-type: none; }
#featured ul li { display: inline; }
/*]]>*/
  </style>
</head>

<body>
  <table width="100%" height="100%">
      <tr>
        <td align=center id='td_top'>
          <table>
              <tr>
                <td>
                  <h1 id="welcomeh1"><l>Welcome to JWChat</l></h1>
                  <h2><l>A web based Jabber/XMPP client</l></h2>
                </td>
              </tr>
          </table>
        </td>
      </tr>
    <tr>
      <td height="100%" align=center valign=top id='td_form'>
  <form name="login" onSubmit="return loginCheck(this);">
        <table border=0 cellspacing=0 cellpadding=2 id="lTable" align=center width=380>
            <tr>
              <td colspan=2><h3><l>Login</l><img src="images/available.gif" width=16 height=16></h3></td>
            </tr>
            <tr id="tr_server" style="display:none;">
              <th title="<l>Select Jabber server to connect to</l>"><label for='server'><l>Server</l></label></th>
              <td></td>
            </tr>
            <tr>
              <th class='form_spacer'><label for='jid'><l>Username</l></label></th>
              <td class='form_spacer' width="100%"><input type="text" id='jid' name="jid" tabindex=3 class='input_text'></td>
            </tr>
            <tr><td>&nbsp;</td><td nowrap class="desc"><l>Username must not contain</l>: <span id='chars_prohibited'></span></td></tr>
            <tr>
              <th><label for='pass'><l>Password</l></label></th>
              <td><input type="password" id='pass' name="pass" tabindex=4 class='input_text'></td>
            </tr>
            <tr><td>&nbsp;</td><td><input type=checkbox name=register id=register> <label for="register"><l>Register New Account</l></label></td></tr>
            <tr id="showMoreOpts" class="toggleOpts">
              <td>&nbsp;</td>
              <td onClick="return toggleMoreOpts(1);"><img src="images/group_close.gif" title="<l>Show More Options</l>"> <l>Show More Options</l></td>
            </tr>
            <tr id="showLessOpts" class="toggleOpts" style="display:none;">
              <td>&nbsp;</td>
              <td onClick="return toggleMoreOpts(0);"><img src="images/group_open.gif" title="<l>Show Fewer Options</l>"> <l>Show Fewer Options</l></td>
            </tr>
            <tr class="moreOpts" style="display:none;">
              <th><label for='res'><l>Resource</l></label></th>
              <td><input type="text" id="res" name="res" class="input_text"></td>
        </tr>
            <tr class="moreOpts" style="display:none;">
              <th><label for='prio'><l>Priority</l></label></th>
              <td>
                <select type="text" id="prio"  name="prio" class="input_text" size="1">
                  <option value="0"><l>low</l></option>
                  <option value="10" selected><l>medium</l></option>
                  <option value="100"><l>high</l></option>
                </select>
              </td>
            </tr>
            <tr class="moreOpts" style="display: none;">
              <th class="form_spacer"><label for="connect_port"><l>Port</l><label></th>
              <td class="form_spacer"><input type="text" name="connect_port" id="connect_port" class="input_text" disabled></td>
            </tr>
            <tr class="moreOpts" style="display: none;">
              <th><label for="connect_host"><l>Connect Host</l></label></th>
              <td><input type="text" name="connect_host" id="connect_host" class="input_text" disabled></td>
            </tr>
            <tr class="moreOpts" style="display: none;">
              <td>&nbsp;</td>
              <td><input type="checkbox" name="connect_secure" id="connect_secure" class="input_text" disabled> <label for="connect_secure" title="<l>Advise connection manager to connect through SSL</l>" disabled><l>Allow secure connections only</l></label></td>
            </tr>
            
            <tr><td>&nbsp;</td><td><button type="submit" id='login_button' tabindex=5 disabled><l>Login</l></button></td></tr>
        </table>
  </form>
<div style="width:400px;">Lost? Click here to <a href="/register/new">register a new account</a>, <a href="/register/change_password">change your password</a> or <a href="/register/delete">delete your existing account</a>!</div>

        </td>
      </tr>
      <tr>
        <td valign="bottom" align="center">
 <div id="featured">
<script type="text/javascript"><!--
google_ad_client = "pub-3213363904178661";
/* jwchat.org */
google_ad_slot = "5628366605";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
    </td>
	  </tr>
    <tr>
      <td id='td_bottom'>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
              <a href="http://blog.jwchat.org/jwchat/">Download</a> | <a href="http://blog.jwchat.org">Blog</a> | <a href="imprint.html"><l>Imprint</l></a> | <a href="about.html"><l>About</l></a>
               <br>
                &copy; 2003-2008 <a href="mailto:steve@zeank.in-berlin.de">Stefan Strigler</a>
            </td>
            <td align="right">
              <a href="http://sourceforge.net/donate/index.php?group_id=92011"><img src="http://images.sourceforge.net/images/project-support.jpg" width="88" height="32" border="0" alt="Support This Project" align="right" /></a>
              <iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fjwchat.org&amp;layout=button_count&amp;show_faces=true&amp;width=90&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:21px;" allowTransparency="true"></iframe>
              <a class="FlattrButton" style="display:none;" rev="flattr;button:compact;"  href="http://jwchat.org"></a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  </body>
</html>