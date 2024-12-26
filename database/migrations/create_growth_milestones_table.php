Schema::create('growth_milestones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('baby_id')->constrained()->onDelete('cascade');
    $table->string('milestone');
    $table->date('achieved_date')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
}); 