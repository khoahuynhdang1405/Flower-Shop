<?php

/*
|--------------------------------------------------------------------------
| Thông báo kiểm tra dữ liệu (tiếng Việt)
|--------------------------------------------------------------------------
|
| Giọng văn mềm mại, gọi khách là "bạn". Rule chưa có ở đây sẽ dùng bản tiếng Anh
| (fallback_locale). Thêm rule mới khi form cần đến.
|
*/

return [

    'accepted' => 'Bạn cần đồng ý với :attribute.',
    'array' => ':Attribute chưa đúng định dạng.',
    'boolean' => ':Attribute chỉ nhận đúng hoặc sai.',
    'confirmed' => ':Attribute nhập lại chưa khớp.',
    'date' => ':Attribute chưa phải là ngày hợp lệ.',
    'date_format' => ':Attribute cần theo định dạng :format.',
    'digits' => ':Attribute cần đủ :digits chữ số.',
    'digits_between' => ':Attribute cần từ :min đến :max chữ số.',
    'email' => ':Attribute chưa đúng định dạng email.',
    'exists' => ':Attribute đã chọn không tồn tại.',
    'image' => ':Attribute cần là một hình ảnh.',
    'in' => ':Attribute đã chọn chưa hợp lệ.',
    'integer' => ':Attribute cần là số nguyên.',
    'max' => [
        'array' => ':Attribute chỉ được tối đa :max mục.',
        'file' => ':Attribute chỉ được tối đa :max KB.',
        'numeric' => ':Attribute không được lớn hơn :max.',
        'string' => ':Attribute chỉ được tối đa :max ký tự.',
    ],
    'mimes' => ':Attribute cần là tệp dạng: :values.',
    'min' => [
        'array' => ':Attribute cần ít nhất :min mục.',
        'file' => ':Attribute cần ít nhất :min KB.',
        'numeric' => ':Attribute cần ít nhất là :min.',
        'string' => ':Attribute cần ít nhất :min ký tự.',
    ],
    'numeric' => ':Attribute cần là số.',
    'password' => [
        'letters' => ':Attribute cần có ít nhất một chữ cái.',
        'mixed' => ':Attribute cần có cả chữ hoa và chữ thường.',
        'numbers' => ':Attribute cần có ít nhất một chữ số.',
        'symbols' => ':Attribute cần có ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':Attribute này đã từng bị lộ trên mạng. Bạn chọn mật khẩu khác giúp Lá nhé.',
    ],
    'regex' => ':Attribute chưa đúng định dạng.',
    'required' => 'Bạn điền :attribute giúp Lá nhé.',
    'same' => ':Attribute và :other cần giống nhau.',
    'string' => ':Attribute cần là chuỗi ký tự.',
    'unique' => ':Attribute này đã được dùng cho một tài khoản khác.',
    'url' => ':Attribute chưa đúng định dạng đường dẫn.',

    'custom' => [],

    'attributes' => [
        'name' => 'họ và tên',
        'email' => 'email',
        'phone' => 'số điện thoại',
        'password' => 'mật khẩu',
        'password_confirmation' => 'xác nhận mật khẩu',
        'terms' => 'điều khoản',
    ],

];
