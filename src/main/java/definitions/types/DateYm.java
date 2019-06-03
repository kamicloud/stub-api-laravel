package definitions.types;

public class DateYm implements CustomizeInterface {
    @Override
    public String getType() {
        return null;
    }

    public String getRule() {
        return "date_format:Y-m";
    }

    @Override
    public TypeSpec getSpec() {
        return TypeSpec.DATE;
    }

    @Override
    public String getParam() {
        return "Y-m";
    }

    @Override
    public String getComment() {
        return "Y-m 例如 2019-01";
    }
}
