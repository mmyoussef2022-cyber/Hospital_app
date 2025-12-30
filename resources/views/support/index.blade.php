@extends('layouts.app')

@section('title', 'الدعم والمساعدة | Support & Help')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-headset me-2"></i>
                        📞 الدعم والمساعدة | Support & Help
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- معلومات الاتصال -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-phone me-2"></i>
                                        معلومات الاتصال | Contact Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>📧 البريد الإلكتروني | Email:</strong>
                                        <br>
                                        <a href="mailto:myoussef400@gmail.com" class="text-primary">
                                            myoussef400@gmail.com
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <strong>📱 موبيل | Mobile:</strong>
                                        <br>
                                        <a href="tel:+21095754085" class="text-primary">
                                            +21095754085
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <strong>🌍 الدولة | Country:</strong>
                                        <br>
                                        مصر | Egypt
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- معلومات المطور -->
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-tie me-2"></i>
                                        معلومات المطور | Developer Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>👨‍💻 المطور | Developer:</strong>
                                        <br>
                                        المهندس محمد يوسف
                                        <br>
                                        <small class="text-muted">Mohamed Youssef</small>
                                    </div>
                                    <div class="mb-3">
                                        <strong>🏢 التخصص | Specialization:</strong>
                                        <br>
                                        تطوير أنظمة إدارة المستشفيات
                                        <br>
                                        <small class="text-muted">Hospital Management Systems Development</small>
                                    </div>
                                    <div class="mb-3">
                                        <strong>🌐 GitHub:</strong>
                                        <br>
                                        <a href="https://github.com/myouseef" target="_blank" class="text-primary">
                                            github.com/myouseef
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- الترخيص -->
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-certificate me-2"></i>
                                        📄 الترخيص | License
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>هذا المشروع مرخص تحت رخصة MIT</strong>
                                        <br>
                                        <small class="text-muted">This project is licensed under the MIT License</small>
                                    </p>
                                    <p class="mb-0">
                                        انظر ملف <code>LICENSE</code> للتفاصيل الكاملة
                                        <br>
                                        <small class="text-muted">See the LICENSE file for full details</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- روابط مفيدة -->
                        <div class="col-12">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-link me-2"></i>
                                        🔗 روابط مفيدة | Useful Links
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <a href="https://github.com/myouseef/Dental_app" target="_blank" class="btn btn-outline-primary btn-block mb-2">
                                                <i class="fab fa-github me-2"></i>
                                                GitHub Repository
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="https://github.com/myouseef/Dental_app/issues" target="_blank" class="btn btn-outline-danger btn-block mb-2">
                                                <i class="fas fa-bug me-2"></i>
                                                الإبلاغ عن مشكلة | Report Issue
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="mailto:myoussef400@gmail.com?subject=Hospital Management System Support" class="btn btn-outline-success btn-block mb-2">
                                                <i class="fas fa-envelope me-2"></i>
                                                إرسال بريد إلكتروني | Send Email
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection