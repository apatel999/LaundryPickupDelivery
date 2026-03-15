using LaundryLoop.Api.Data;

var builder = WebApplication.CreateBuilder(args);

// ── Services ──────────────────────────────────
builder.Services.AddControllers();

// Swagger
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen(c =>
    c.SwaggerDoc("v1", new() { Title = "LaundryLoop API", Version = "v1" }));

// Dapper — register repository as a scoped service
// IConfiguration is injected automatically by the framework
builder.Services.AddScoped<BookingRepository>();

// CORS — allow the HTML frontend to call the API
// TODO: replace AllowAnyOrigin with your actual domain before going live
builder.Services.AddCors(options =>
    options.AddPolicy("FrontendPolicy", policy =>
        policy.AllowAnyOrigin().AllowAnyHeader().AllowAnyMethod()));

// ── Pipeline ──────────────────────────────────
var app = builder.Build();

if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseCors("FrontendPolicy");
app.UseAuthorization();
app.MapControllers();

app.Run();
