<?php

return [
    'title' => 'कर्मचारी व उपयोगकर्ता प्रबंधन',
    'subtitle' => 'आंतरिक कर्मचारी खाते, पदनाम, अनुमतियां, पासवर्ड सुरक्षा और सिस्टम पहुंच को प्रबंधित करें।',
    'banner_total' => ':count कर्मचारी खाते',
    'banner_active' => ':count सक्रिय',
    'banner_admins' => ':count एडमिन',
    'banner_recent_login' => ':count हालिया लॉगिन (7 दिन)',

    // Action buttons & shortcuts
    'btn_add_user' => 'नया कर्मचारी जोड़ें',
    'btn_roles' => 'भूमिकाएं व अनुमतियां',
    'btn_audit_logs' => 'ऑडिट ट्रेल लॉग्स',
    'btn_settings' => 'सिस्टम सेटिंग्स',
    'btn_export_csv' => 'CSV निर्यात',

    // 4 KPI Metric Cards
    'kpi_total_users' => 'कुल कर्मचारी खाते',
    'kpi_total_users_sub' => 'संस्थान के कुल अधिकृत उपयोगकर्ता',
    'kpi_active_users' => 'सक्रिय कर्मचारी',
    'kpi_active_users_sub' => 'सिस्टम लॉगिन हेतु अनुमत खाते',
    'kpi_admins' => 'सिस्टम एडमिनिस्ट्रेटर',
    'kpi_admins_sub' => 'पूर्ण प्रशासनिक व वित्तीय अधिकार',
    'kpi_temp_passwords' => 'अस्थायी पासवर्ड',
    'kpi_temp_passwords_sub' => 'पहले लॉगिन पर पासवर्ड बदलना अनिवार्य',

    // Filter Tabs
    'tab_all' => 'सभी कर्मचारी',
    'tab_active' => 'सक्रिय खाते',
    'tab_inactive' => 'निष्क्रिय',
    'tab_admins' => 'एडमिनिस्ट्रेटर',
    'tab_temp_pwd' => 'अस्थायी पासवर्ड',

    // Search & Filters
    'filter_search' => 'स्टाफ खोजें',
    'search_placeholder' => 'नाम, मोबाइल, ईमेल या पदनाम से खोजें...',
    'filter_role' => 'सिस्टम भूमिका',
    'role_all' => 'सभी भूमिकाएं',
    'filter_status' => 'स्थिति',
    'status_all' => 'सभी स्थितियाँ',
    'status_active' => 'केवल सक्रिय',
    'status_inactive' => 'केवल निष्क्रिय',
    'btn_filter' => 'फिल्टर करें',
    'btn_reset' => 'रीसेट',

    // Table Columns
    'col_user' => 'कर्मचारी / संपर्क विवरण',
    'col_role' => 'सिस्टम भूमिका',
    'col_designation' => 'पदनाम (Designation)',
    'col_status' => 'खाता स्थिति',
    'col_last_active' => 'अंतिम सक्रियता',
    'col_actions' => 'कार्यवाई',

    // Badges & Labels
    'status_active_pill' => 'सक्रिय',
    'status_inactive_pill' => 'निष्क्रिय',
    'temp_pwd_pill' => 'अस्थायी पासवर्ड',
    'never_logged_in' => 'कभी लॉगिन नहीं किया',
    'btn_edit' => 'संशोधित करें',
    'btn_activate' => 'सक्रिय करें',
    'btn_deactivate' => 'निष्क्रिय करें',
    'confirm_toggle' => 'क्या आप निश्चित रूप से इस उपयोगकर्ता खाते की स्थिति बदलना चाहते हैं?',
    'empty_title' => 'कोई कर्मचारी खाता नहीं मिला',
    'empty_text' => 'आपकी खोज या चुने गए फ़िल्टर के अनुसार कोई खाता उपलब्ध नहीं है।',
    'btn_create_first' => 'पहला कर्मचारी खाता बनाएं',

    // Form labels
    'form_create_title' => 'नया कर्मचारी खाता बनाएं',
    'form_edit_title' => 'कर्मचारी खाता संशोधित करें: :name',
    'form_create_subtitle' => 'मोबाइल लॉगिन और भूमिका अनुमतियों के साथ नया टीम सदस्य पंजीकृत करें।',
    'form_edit_subtitle' => 'कर्मचारी विवरण, विभाग पदनाम और सिस्टम अनुमतियों को अद्यतन करें।',
    'label_name' => 'पूरा नाम',
    'placeholder_name' => 'उदा. रमेश कुमार',
    'label_mobile' => 'मोबाइल नंबर (लॉगिन आईडी)',
    'placeholder_mobile' => '10 अंकों का मोबाइल नंबर',
    'help_mobile' => 'कर्मचारी द्वारा ईआरपी लॉगिन हेतु उपयोग किया जाने वाला 10 अंकों का मोबाइल नंबर।',
    'label_email' => 'ईमेल पता',
    'placeholder_email' => 'वैकल्पिक — उदा. ramesh@kisan.local',
    'help_email' => 'वैकल्पिक। पासवर्ड रीसेट व आधिकारिक सूचनाओं हेतु प्रयुक्त।',
    'label_designation' => 'पदनाम / भूमिका',
    'placeholder_designation' => 'उदा. लेखापाल (Accounts), मंडी प्रभारी',
    'label_role' => 'सिस्टम भूमिका (Role)',
    'help_role' => 'यह तय करता है कि उपयोगकर्ता कौन से मॉड्यूल व रिकॉर्ड्स देख या संशोधित कर सकता है।',
    'label_is_active' => 'खाता सक्रिय है',
    'help_is_active' => 'निष्क्रिय उपयोगकर्ता सिस्टम में लॉगिन नहीं कर सकते।',
    'section_password' => 'सुरक्षा व पासवर्ड सेटअप',
    'label_password' => 'पासवर्ड',
    'placeholder_password' => 'न्यूनतम 8 अक्षर',
    'label_password_confirmation' => 'पासवर्ड की पुष्टि करें',
    'placeholder_password_confirmation' => 'पासवर्ड पुनः दर्ज करें',
    'help_password_create' => 'अस्थायी पासवर्ड। कर्मचारी को पहली बार लॉगिन करने पर इसे बदलना होगा।',
    'help_password_edit' => 'वर्तमान पासवर्ड बनाए रखने हेतु इसे खाली छोड़ दें।',
    'btn_save' => 'परिवर्तन सहेजें',
    'btn_create' => 'कर्मचारी खाता बनाएं',
    'btn_cancel' => 'रद्द करें',
    'btn_back' => 'वापस सूची पर जाएं',
];

